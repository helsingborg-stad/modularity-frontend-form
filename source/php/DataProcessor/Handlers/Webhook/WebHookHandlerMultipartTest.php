<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WebHookHandler;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use WP_Error;
use WP_REST_Request;
use WpService\WpService;

/**
 * Records handler errors without the enum round-trip of HandlerResult, which
 * requires a real WP runtime to resolve the error code.
 */
final class RecordingHandlerResult implements HandlerResultInterface
{
    /** @var array<int, WP_Error> */
    public array $errors = [];

    public function isOk(): bool
    {
        return $this->errors === [];
    }

    public function getErrors(): ?array
    {
        return $this->errors ?: null;
    }

    public function setError(WP_Error $error): void
    {
        $this->errors[] = $error;
    }
}

final class WebHookHandlerMultipartTest extends TestCase
{
    /** @var array<int, array{url:string, args:array<string, mixed>}> */
    private array $requests = [];

    /** @var array<int, array<string, mixed>> */
    private array $responses = [];

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

    public function testMultipartPayloadOmitsLegacyCatchAllKeyAndSendsReferenceAndFile(): void
    {
        $handler = $this->createHandler(
            $this->multipartConfig(['header' => 'Authorization', 'value' => 'secret']),
            $this->snapshotsForSingleImage()
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertTrue($result?->isOk(), 'Handler should succeed on an HTTP 200 webhook response.');
        self::assertCount(1, $this->requests);

        $request = $this->requests[0];
        $body    = (string) $request['args']['body'];
        $headers = $request['args']['headers'];

        // The catch-all "*" duplication must not become transmitted fields.
        self::assertStringNotContainsString('name="*"', $body);
        self::assertStringNotContainsString('name="[', $body);

        self::assertStringContainsString('name="acf[image]"', $body);
        self::assertStringContainsString("\r\n\r\n\$file:file_0\r\n", $body);
        self::assertStringContainsString('name="_acf_rest_files[file_0]"', $body);

        // Protocol headers are controlled by the sender; the configured
        // Content-Type must not win and other headers are kept.
        self::assertArrayHasKey('Idempotency-Key', $headers);
        self::assertSame('1', $headers['X-ACF-Rest-Upload-Version'] ?? null);
        self::assertStringStartsWith('multipart/form-data; boundary=', (string) $headers['Content-Type']);
        self::assertSame('secret', $headers['Authorization'] ?? null);
    }

    public function testMixedGalleryMergesReferencesWithoutDiscardingExistingIds(): void
    {
        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForGalleryItemAt(1)
        );

        $result = $handler->handle(['acf' => ['gallery' => ['111']]], new WP_REST_Request());

        self::assertTrue($result?->isOk());
        self::assertCount(1, $this->requests);

        $body = (string) $this->requests[0]['args']['body'];

        // Existing destination attachment ID stays at its position.
        self::assertStringContainsString('name="acf[gallery][0]"', $body);
        self::assertStringContainsString("\r\n\r\n111\r\n", $body);

        // The new upload lands at its original gallery index.
        self::assertStringContainsString('name="acf[gallery][1]"', $body);
        self::assertStringContainsString("\r\n\r\n\$file:file_0\r\n", $body);
    }

    public function testNullAndFileReferenceConflictAbortsBeforeSend(): void
    {
        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForSingleImage()
        );

        $result = $handler->handle(['acf' => ['image' => null]], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(0, $this->requests, 'A null/reference conflict must abort before any request.');
    }

    public function testRecognized409InProgressConflictIsRetriedThenSucceeds(): void
    {
        $this->responses = [
            [
                'response' => ['code' => 409],
                'headers'  => ['retry-after' => '0'],
                'body'     => '{"code":"acf_rest_upload_in_progress","message":"An upload for this idempotency key is still in progress.","data":{"status":409}}',
            ],
            ['response' => ['code' => 200], 'headers' => [], 'body' => '{"id":1}'],
        ];

        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForSingleImage()
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertTrue($result?->isOk());
        self::assertCount(2, $this->requests, 'The recognized in-progress conflict must be retried once.');
    }

    public function testUnrecognized409IsNotRetried(): void
    {
        $this->responses = [
            [
                'response' => ['code' => 409],
                'headers'  => ['retry-after' => '0'],
                'body'     => '{"code":"rest_invalid_param","message":"Invalid parameter."}',
            ],
            ['response' => ['code' => 200], 'headers' => [], 'body' => '{"id":1}'],
        ];

        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForSingleImage()
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(1, $this->requests, 'An unrecognized 409 must fail without retry.');
    }

    public function testSnapshotFailureAbortsBeforeSend(): void
    {
        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForUnreadableUpload()
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(0, $this->requests, 'A failed upload snapshot must abort the send.');

        // The handler error is generic: upload names and field keys from the
        // failed snapshot are not exposed.
        $encoded = json_encode($result?->getErrors() ?? [], JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '';

        self::assertStringNotContainsString('photo.jpg', $encoded);
        self::assertStringNotContainsString('field_image', $encoded);
    }

    public function testUploadsAboveAggregateLimitAbortBeforeSend(): void
    {
        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForSingleImage(1024 * 1024),
            ['wpMaxUploadSize' => 10]
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(0, $this->requests, 'Uploads above the aggregate limit must abort the send.');
    }

    public function testNonSuccessResponseIsAnErrorAndRawResponseIsNotAttached(): void
    {
        $this->responses = [
            [
                'response' => ['code' => 422],
                'headers'  => [],
                'body'     => '{"secret-response-body":"sensitive"}',
            ],
        ];

        $handler = $this->createHandler(
            $this->multipartConfig(),
            $this->snapshotsForSingleImage()
        );

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(1, $this->requests);

        $encoded = json_encode($result?->getErrors() ?? [], JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '';

        self::assertStringNotContainsString('secret-response-body', $encoded);
        self::assertStringNotContainsString('WP_Error', $encoded);
    }

    public function testJsonModeKeepsLegacyCatchAllKeyAndProtocolHeadersAbsent(): void
    {
        $config = $this->multipartConfig();
        $config->requestFormat = 'json';
        $config->body = '{"acf":{"image":"{{image}}"},"all":"{{*}}"}';

        $handler = $this->createHandler($config, null);

        $result = $handler->handle(['acf' => ['image' => '999']], new WP_REST_Request());

        self::assertTrue($result?->isOk());
        self::assertCount(1, $this->requests);

        $request = $this->requests[0];
        $body    = (string) $request['args']['body'];
        $headers = $request['args']['headers'];

        $decoded = json_decode($body, true);

        self::assertIsArray($decoded);
        self::assertSame(['image' => '999'], $decoded['all'], 'Legacy JSON templates can hydrate all fields through {{*}}.');
        self::assertSame('999', $decoded['acf']['image']);
        self::assertSame('application/json', $headers['Content-Type'] ?? null);
        self::assertArrayNotHasKey('Idempotency-Key', $headers);
        self::assertArrayNotHasKey('X-ACF-Rest-Upload-Version', $headers);
    }

    private function createHandler(
        object $config,
        ?UploadedFileSnapshots $snapshots,
        array $wpServiceOverrides = []
    ): WebHookHandler {
        $wpService = $this->createMock(WpService::class);

        $wpService->method('wpRemotePost')->willReturnCallback(
            function (string $url, array $args) {
                $this->requests[] = ['url' => $url, 'args' => $args];

                $index = count($this->requests) - 1;

                return $this->responses[min($index, count($this->responses) - 1)]
                    ?? ['response' => ['code' => 200], 'headers' => [], 'body' => ''];
            }
        );

        $wpService->method('isWpError')->willReturn(false);
        $wpService->method('wpRemoteRetrieveResponseCode')->willReturnCallback(
            static fn($response): int => (int) ($response['response']['code'] ?? 500)
        );
        $wpService->method('wpRemoteRetrieveHeaders')->willReturnCallback(
            static fn($response): array => is_array($response['headers'] ?? null) ? $response['headers'] : []
        );
        $wpService->method('wpRemoteRetrieveBody')->willReturnCallback(
            static fn($response): string => is_string($response['body'] ?? null) ? $response['body'] : ''
        );
        $wpService->method('wpMaxUploadSize')->willReturn(
            $wpServiceOverrides['wpMaxUploadSize'] ?? 32 * 1024 * 1024
        );

        $acfService = $this->createMock(AcfService::class);
        $acfService->method('getFieldObject')->willReturnCallback(
            static fn($key): array => [
                'key'  => $key,
                'name' => str_replace('field_', '', (string) $key),
                'type' => 'image',
            ]
        );

        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('getFieldNamespace')->willReturn('acf');

        $moduleConfig = $this->createMock(ModuleConfigInterface::class);
        $moduleConfig->method('getWebHookHandlerConfig')->willReturn($config);

        return new WebHookHandler(
            $wpService,
            $acfService,
            $configMock,
            $moduleConfig,
            (object) [],
            new RecordingHandlerResult(),
            new NullLogger(),
            $this->createMock(FileHandlerInterface::class),
            $snapshots
        );
    }

    private function multipartConfig(?array $extraHeader = null): object
    {
        $headers = [
            ['header' => 'Authorization', 'value' => 'secret'],
            // Reserved headers are controlled by the sender; matching is
            // whitespace tolerant.
            ['header' => ' Content-Type ', 'value' => 'text/plain'],
            ['header' => ' X-ACF-Rest-Upload-Version ', 'value' => '9'],
        ];

        if ($extraHeader !== null) {
            $headers[] = $extraHeader;
        }

        return (object) [
            'callbackUrl'   => 'https://destination.test/wp-json/wp/v2/sponsor-assignments',
            'requestFormat' => 'multipart',
            'body'          => '{"acf":{"image":"{{image}}","gallery":"{{gallery}}"}}',
            'headers'       => $headers,
            'timeout'       => 5,
        ];
    }

    private function snapshotsForSingleImage(int $size = 4): UploadedFileSnapshots
    {
        return new UploadedFileSnapshots(
            [
                'name'     => ['field_image' => 'photo.jpg'],
                'type'     => ['field_image' => 'image/jpeg'],
                'tmp_name' => ['field_image' => $this->makeTempFile('aaaa')],
                'error'    => ['field_image' => UPLOAD_ERR_OK],
                'size'     => ['field_image' => $size],
            ],
            $this->createAcfServiceForSnapshotNames()
        );
    }

    private function snapshotsForGalleryItemAt(int $index): UploadedFileSnapshots
    {
        return new UploadedFileSnapshots(
            [
                'name'     => ['field_gallery' => [$index => 'new.jpg']],
                'type'     => ['field_gallery' => [$index => 'image/jpeg']],
                'tmp_name' => ['field_gallery' => [$index => $this->makeTempFile('bbbb')]],
                'error'    => ['field_gallery' => [$index => UPLOAD_ERR_OK]],
                'size'     => ['field_gallery' => [$index => 4]],
            ],
            $this->createAcfServiceForSnapshotNames()
        );
    }

    private function snapshotsForUnreadableUpload(): UploadedFileSnapshots
    {
        return new UploadedFileSnapshots(
            [
                'name'     => ['field_image' => 'photo.jpg'],
                'type'     => ['field_image' => 'image/jpeg'],
                'tmp_name' => ['field_image' => '/nonexistent/photo.jpg'],
                'error'    => ['field_image' => UPLOAD_ERR_OK],
                'size'     => ['field_image' => 4],
            ],
            $this->createAcfServiceForSnapshotNames()
        );
    }

    private function createAcfServiceForSnapshotNames(): AcfService
    {
        $acfService = $this->createMock(AcfService::class);
        $acfService->method('getFieldObject')->willReturnCallback(
            static fn($key): array => [
                'key'  => $key,
                'name' => str_replace('field_', '', (string) $key),
                'type' => 'image',
            ]
        );

        return $acfService;
    }

    private function makeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'webhook-handler-test-');

        if ($path === false) {
            self::fail('Unable to create a temporary file for the test.');
        }

        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }
}
