<?php

declare(strict_types=1);

namespace ModularityFrontendForm\Helper;

use ModularityFrontendForm\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;

final class FilesArrayFormatterTest extends TestCase
{
    public function testFormatsSingleFileWithEveryWordPressFileProperty(): void
    {
        $formatter = $this->createFormatter([
            'name' => ['field_file' => 'report.pdf'],
            'type' => ['field_file' => 'application/pdf'],
            'tmp_name' => ['field_file' => '/tmp/php-single'],
            'error' => ['field_file' => UPLOAD_ERR_OK],
            'size' => ['field_file' => 123],
        ]);

        self::assertSame([
            'field_file' => [[
                'name' => 'report.pdf',
                'type' => 'application/pdf',
                'tmp_name' => '/tmp/php-single',
                'error' => UPLOAD_ERR_OK,
                'size' => 123,
            ]],
        ], $formatter->getFormatted());
    }

    public function testFormatsMultipleFilesWithoutMixingTheirProperties(): void
    {
        $formatter = $this->createFormatter([
            'name' => ['field_files' => ['first.pdf', 'second.pdf']],
            'type' => ['field_files' => ['application/pdf', 'application/pdf']],
            'tmp_name' => ['field_files' => ['/tmp/php-first', '/tmp/php-second']],
            'error' => ['field_files' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK]],
            'size' => ['field_files' => [123, 456]],
        ]);

        self::assertSame('/tmp/php-first', $formatter->getFormatted()['field_files'][0]['tmp_name']);
        self::assertSame('/tmp/php-second', $formatter->getFormatted()['field_files'][1]['tmp_name']);
        self::assertSame(456, $formatter->getFormatted()['field_files'][1]['size']);
    }

    public function testIgnoresEmptyOptionalFileFields(): void
    {
        $formatter = $this->createFormatter([
            'name' => ['field_optional' => ['']],
            'type' => ['field_optional' => ['']],
            'tmp_name' => ['field_optional' => ['']],
            'error' => ['field_optional' => [UPLOAD_ERR_NO_FILE]],
            'size' => ['field_optional' => [0]],
        ]);

        self::assertNull($formatter->getFormatted());
    }

    public function testPreservesMissingTemporaryFilenameForValidation(): void
    {
        $formatter = $this->createFormatter([
            'name' => ['field_file' => ['report.pdf']],
            'type' => ['field_file' => ['application/pdf']],
            'tmp_name' => ['field_file' => ['']],
            'error' => ['field_file' => [UPLOAD_ERR_OK]],
            'size' => ['field_file' => [123]],
        ]);

        self::assertSame('', $formatter->getFormatted()['field_file'][0]['tmp_name']);
    }

    private function createFormatter(array $files): FilesArrayFormatter
    {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => $files]);
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('acf');

        return new FilesArrayFormatter($request, $config);
    }
}
