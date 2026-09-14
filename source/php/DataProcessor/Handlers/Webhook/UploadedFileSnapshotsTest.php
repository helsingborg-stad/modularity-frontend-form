<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use PHPUnit\Framework\TestCase;

final class UploadedFileSnapshotsTest extends TestCase
{
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

    public function testSnapshotsSingleUploadWithDeterministicKey(): void
    {
        $path = $this->makeTempFile('image-bytes');

        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_image' => 'photo.jpg'],
            'type'     => ['field_image' => 'image/jpeg'],
            'tmp_name' => ['field_image' => $path],
            'error'    => ['field_image' => UPLOAD_ERR_OK],
            'size'     => ['field_image' => 11],
        ]);

        $map = $snapshots->getFileMap();

        self::assertArrayHasKey('file_0', $map);
        self::assertNotSame($path, $map['file_0']['tmp_name']);
        self::assertSame('photo.jpg', $map['file_0']['name']);
        self::assertSame('image/jpeg', $map['file_0']['type']);
        self::assertSame('image-bytes', file_get_contents($map['file_0']['tmp_name']));

        // With no AcfService the converted name falls back to the field key.
        self::assertSame('$file:file_0', $snapshots->getReferences()['field_image']);

        $snapshotPath = $map['file_0']['tmp_name'];
        $snapshots->cleanup();

        self::assertFileDoesNotExist($snapshotPath);
    }

    public function testGalleryUploadsBecomeIndexedReferenceList(): void
    {
        $first  = $this->makeTempFile('first');
        $second = $this->makeTempFile('second');

        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_gallery' => ['first.jpg', 'second.jpg']],
            'type'     => ['field_gallery' => ['image/jpeg', 'image/jpeg']],
            'tmp_name' => ['field_gallery' => [$first, $second]],
            'error'    => ['field_gallery' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK]],
            'size'     => ['field_gallery' => [5, 6]],
        ]);

        self::assertSame(
            [0 => '$file:file_0', 1 => '$file:file_1'],
            $snapshots->getReferences()['field_gallery']
        );
        self::assertCount(2, $snapshots->getFileMap());

        $snapshots->cleanup();
    }

    public function testSparseGalleryUploadsKeepTheirOriginalIndexes(): void
    {
        $first = $this->makeTempFile('first');
        $third = $this->makeTempFile('third');

        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_gallery' => [0 => 'first.jpg', 2 => 'third.jpg']],
            'type'     => ['field_gallery' => [0 => 'image/jpeg', 2 => 'image/jpeg']],
            'tmp_name' => ['field_gallery' => [0 => $first, 2 => $third]],
            'error'    => ['field_gallery' => [0 => UPLOAD_ERR_OK, 2 => UPLOAD_ERR_OK]],
            'size'     => ['field_gallery' => [0 => 5, 2 => 5]],
        ]);

        self::assertSame(
            [0 => '$file:file_0', 2 => '$file:file_1'],
            $snapshots->getReferences()['field_gallery']
        );

        $snapshots->cleanup();
    }

    public function testUnreadableSelectedUploadIsRecordedAsFailure(): void
    {
        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_image' => 'broken.jpg'],
            'type'     => ['field_image' => 'image/jpeg'],
            'tmp_name' => ['field_image' => '/nonexistent/broken.jpg'],
            'error'    => ['field_image' => UPLOAD_ERR_OK],
            'size'     => ['field_image' => 0],
        ]);

        self::assertTrue($snapshots->hasFailures());

        self::assertSame([], $snapshots->getFileMap());
        self::assertSame([], $snapshots->getReferences());

        $snapshots->cleanup();
    }

    public function testSnapshotsScalarSingleFileLayoutUnderStableKey(): void
    {
        $path = $this->makeTempFile('scalar-bytes');

        $snapshots = new UploadedFileSnapshots([
            'name'     => 'photo.jpg',
            'type'     => 'image/jpeg',
            'tmp_name' => $path,
            'error'    => UPLOAD_ERR_OK,
            'size'     => 12,
        ]);

        $map = $snapshots->getFileMap();

        self::assertArrayHasKey('file_0', $map);
        self::assertNotSame($path, $map['file_0']['tmp_name']);
        self::assertSame('photo.jpg', $map['file_0']['name']);
        self::assertSame('image/jpeg', $map['file_0']['type']);
        self::assertSame('scalar-bytes', file_get_contents($map['file_0']['tmp_name']));
        self::assertSame('$file:file_0', $snapshots->getReferences()['file']);

        $snapshots->cleanup();
    }

    public function testInvalidUploadsAreIgnored(): void
    {
        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_image' => 'broken.jpg'],
            'type'     => ['field_image' => 'image/jpeg'],
            'tmp_name' => ['field_image' => '/nonexistent/broken.jpg'],
            'error'    => ['field_image' => UPLOAD_ERR_CANT_WRITE],
            'size'     => ['field_image' => 0],
        ]);

        self::assertSame([], $snapshots->getFileMap());
        self::assertSame([], $snapshots->getReferences());
        self::assertFalse($snapshots->hasFiles());

        // A failed upload was never selected, so it is not a snapshot failure.
        self::assertFalse($snapshots->hasFailures());
    }

    public function testCleanupIsIdempotent(): void
    {
        $path = $this->makeTempFile('bytes');

        $snapshots = new UploadedFileSnapshots([
            'name'     => ['field_file' => 'doc.pdf'],
            'type'     => ['field_file' => 'application/pdf'],
            'tmp_name' => ['field_file' => $path],
            'error'    => ['field_file' => UPLOAD_ERR_OK],
            'size'     => ['field_file' => 5],
        ]);

        $snapshots->cleanup();
        $snapshots->cleanup();

        self::assertFalse($snapshots->hasFiles());
    }

    private function makeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mff-snapshot-test-');
        if ($path === false) {
            self::fail('Unable to create a temporary file for the test.');
        }

        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }
}
