<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MultipartFormDataEncoderTest extends TestCase
{
    private const BOUNDARY = 'TEST-BOUNDARY-123';

    /** @var array<int, string> */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        $this->tempFiles = [];
    }

    public function testGeneratesRandomBoundaryAndContentType(): void
    {
        $first = new MultipartFormDataEncoder();
        $second = new MultipartFormDataEncoder();

        self::assertNotSame($first->boundary(), $second->boundary());
        self::assertStringStartsWith('multipart/form-data; boundary=', $first->contentType());
        self::assertStringContainsString($first->boundary(), $first->contentType());
    }

    public function testFlattensNestedScalarsAndLists(): void
    {
        $result = (new MultipartFormDataEncoder(self::BOUNDARY))->encode([
            'title' => 'Hello',
            'count' => 3,
            'ratio' => 1.5,
            'active' => true,
            'meta' => ['color' => 'red', 'tags' => ['a', 'b']],
        ]);

        self::assertSame(self::BOUNDARY, $result['boundary']);
        self::assertSame('multipart/form-data; boundary=' . self::BOUNDARY, $result['contentType']);

        $body = $result['body'];

        self::assertStringContainsString('name="title"', $body);
        self::assertStringContainsString("\r\n\r\nHello\r\n", $body);
        self::assertStringContainsString('name="count"', $body);
        self::assertStringContainsString("\r\n\r\n3\r\n", $body);
        self::assertStringContainsString('name="ratio"', $body);
        self::assertStringContainsString("\r\n\r\n1.5\r\n", $body);
        self::assertStringContainsString('name="active"', $body);
        self::assertStringContainsString("\r\n\r\n1\r\n", $body);
        self::assertStringContainsString('name="meta[color]"', $body);
        self::assertStringContainsString('name="meta[tags][]"', $body);
        self::assertStringEndsWith('--' . self::BOUNDARY . "--\r\n", $body);
    }

    public function testPreservesNullsAsPathsInsteadOfParameters(): void
    {
        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode([
            'title' => 'Hello',
            'attachment' => null,
            'meta' => ['summary' => null, 'list' => [null]],
        ])['body'];

        self::assertStringNotContainsString('name="attachment"', $body);
        self::assertStringNotContainsString('name="meta[summary]"', $body);
        self::assertStringNotContainsString('name="meta[list][]"', $body);
        self::assertStringContainsString('name="_acf_rest_nulls[]"', $body);

        // Root null paths stay unbracketed, nested paths keep their brackets.
        self::assertStringContainsString("\r\n\r\nattachment\r\n", $body);
        self::assertStringNotContainsString("\r\n\r\n[attachment]\r\n", $body);
        self::assertStringContainsString("\r\n\r\nmeta[summary]\r\n", $body);
        self::assertStringContainsString("\r\n\r\nmeta[list][]\r\n", $body);
    }

    public function testIncludesReferencedBinaryOnceAndExcludesUnreferencedFiles(): void
    {
        $referenced = $this->makeTempFile('binary-content');
        $unreferenced = $this->makeTempFile('should-not-appear');

        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode(
            [
                'avatar' => MultipartFormDataEncoder::FILE_REFERENCE_PREFIX . 'abc',
                'thumbnail' => MultipartFormDataEncoder::FILE_REFERENCE_PREFIX . 'abc',
                'note' => 'plain',
            ],
            [
                'abc' => [
                    'name' => 'avatar.png',
                    'type' => 'image/png',
                    'tmp_name' => $referenced,
                    'size' => 14,
                ],
                'zzz' => [
                    'name' => 'unused.txt',
                    'type' => 'text/plain',
                    'tmp_name' => $unreferenced,
                    'size' => 17,
                ],
            ]
        )['body'];

        // The token stays in the payload so the receiver can map fields to files.
        self::assertStringContainsString('name="avatar"', $body);
        self::assertStringContainsString('$file:abc', $body);
        self::assertStringContainsString('name="thumbnail"', $body);

        self::assertStringContainsString('name="_acf_rest_files[abc]"', $body);
        self::assertStringContainsString('filename="avatar.png"', $body);
        self::assertStringContainsString('Content-Type: image/png', $body);
        self::assertStringContainsString('binary-content', $body);

        // Binary is emitted only once for a key referenced by two fields.
        self::assertSame(1, substr_count($body, 'binary-content'));

        self::assertStringNotContainsString('should-not-appear', $body);
        self::assertStringNotContainsString('_acf_rest_files[zzz]', $body);
    }

    public function testBodyIsBinarySafe(): void
    {
        $binary = "\x00\x01\x02\r\nraw\xff\xfe";
        $path = $this->makeTempFile($binary);

        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode(
            ['file' => '$file:bin'],
            ['bin' => ['name' => 'data.bin', 'type' => 'application/octet-stream', 'tmp_name' => $path, 'size' => strlen($binary)]]
        )['body'];

        self::assertStringContainsString($binary, $body);
    }

    public function testThrowsWhenReferencedFileIsAbsentFromMap(): void
    {
        $encoder = new MultipartFormDataEncoder(self::BOUNDARY);

        $this->expectException(InvalidArgumentException::class);
        $encoder->encode(['avatar' => '$file:missing'], []);
    }

    public function testThrowsWhenReferencedFileIsUnreadable(): void
    {
        $encoder = new MultipartFormDataEncoder(self::BOUNDARY);

        $this->expectException(InvalidArgumentException::class);
        $encoder->encode(
            ['avatar' => '$file:abc'],
            ['abc' => ['name' => 'x.txt', 'type' => 'text/plain', 'tmp_name' => '/nonexistent/path/x.txt', 'size' => 1]]
        );
    }

    public function testEmptyKeyTokenIsTreatedAsOrdinaryString(): void
    {
        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode([
            'value' => '$file:',
        ])['body'];

        self::assertStringContainsString('name="value"', $body);
        self::assertStringContainsString("\r\n\r\n\$file:\r\n", $body);
        self::assertStringNotContainsString('_acf_rest_files[', $body);
    }

    public function testUnsafeFileMarkerIsTreatedAsOrdinaryString(): void
    {
        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode([
            'value' => '$file:foo!',
            'spaced' => '$file:foo bar',
        ])['body'];

        self::assertStringContainsString('name="value"', $body);
        self::assertStringContainsString("\r\n\r\n\$file:foo!\r\n", $body);
        self::assertStringContainsString('name="spaced"', $body);
        self::assertStringContainsString("\r\n\r\n\$file:foo bar\r\n", $body);
        self::assertStringNotContainsString('_acf_rest_files[', $body);
    }

    public function testSafeFileMarkerKeyIsRecognized(): void
    {
        $path = $this->makeTempFile('bytes');

        $body = (new MultipartFormDataEncoder(self::BOUNDARY))->encode(
            ['ok' => '$file:file_0-aB9'],
            ['file_0-aB9' => ['name' => 'x.txt', 'type' => 'text/plain', 'tmp_name' => $path, 'size' => 5]]
        )['body'];

        self::assertStringContainsString('name="_acf_rest_files[file_0-aB9]"', $body);
    }

    private function makeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'webhook-multipart-');
        if ($path === false) {
            self::fail('Unable to create a temporary file for the test.');
        }

        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }
}
