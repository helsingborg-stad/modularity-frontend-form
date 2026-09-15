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

    /** @var array<int, array<string, mixed>|WP_Error> */
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

    public function testMultipartCreateSendsVersionTwoAndSelectedBytesWithoutIdempotency(): void
    {
        $config = $this->multipartConfig(['header' => ' IdEmPoTeNcY-KeY ', 'value' => 'ignored']);
        $config->requestFormat = 'multipart-create';
        $config->body = '{"title":"Create example","acf":{"image":"{{image}}"}}';
        $snapshots = $this->snapshotsForSingleImage();
        $paths = array_column($snapshots->getFileMap(), 'tmp_name');
        $result = $this->createHandler($config, $snapshots)->handle([], new WP_REST_Request());

        self::assertTrue($result?->isOk());
        self::assertCount(1, $this->requests);
        $args = $this->requests[0]['args'];
        self::assertSame('2', $args['headers']['X-ACF-Rest-Upload-Version'] ?? null);
        self::assertArrayNotHasKey('idempotency-key', array_change_key_case($args['headers']));
        $parts = $this->captureMultipartParts($args);
        self::assertSame('Create example', $parts['title']);
        self::assertSame('aaaa', $this->referencedBinary($parts, 'acf[image]'));
        foreach ($paths as $path) {
            self::assertFileDoesNotExist($path);
        }
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

    /** @dataProvider createFailureProvider */
    public function testMultipartCreateFailsAfterOneAttempt(?int $status): void
    {
        $error = $this->createMock(WP_Error::class);
        $error->method('get_error_message')->willReturn('Transport timed out.');
        $this->responses = [$status === null ? $error : [
            'response' => ['code' => $status],
            'headers' => ['retry-after' => '0'],
            'body' => '{"code":"acf_rest_upload_unsupported_version"}',
        ]];
        $config = $this->multipartConfig();
        $config->requestFormat = 'multipart-create';
        $snapshots = $this->snapshotsForSingleImage();
        $paths = array_column($snapshots->getFileMap(), 'tmp_name');

        $result = $this->createHandler($config, $snapshots)->handle([], new WP_REST_Request());

        self::assertFalse($result?->isOk());
        self::assertCount(1, $result?->getErrors());
        self::assertCount(1, $this->requests, 'No transport retry or JSON fallback is permitted.');
        self::assertSame('2', $this->requests[0]['args']['headers']['X-ACF-Rest-Upload-Version']);
        foreach ($paths as $path) {
            self::assertFileDoesNotExist($path);
        }
    }

    public static function createFailureProvider(): array
    {
        return [
            'transport error' => [null],
            'incompatible version' => [400],
            'too early' => [425],
            'rate limited' => [429],
            'internal error' => [500],
            'bad gateway' => [502],
            'unavailable' => [503],
            'gateway timeout' => [504],
        ];
    }

    public function testMultipartCreateSnapshotFailurePreventsTransport(): void
    {
        $config = $this->multipartConfig();
        $config->requestFormat = 'multipart-create';
        $result = $this->createHandler($config, $this->snapshotsForUnreadableUpload())
            ->handle([], new WP_REST_Request());
        self::assertFalse($result?->isOk());
        self::assertSame([], $this->requests);
    }

    /** @dataProvider createLimitProvider */
    public function testMultipartCreatePreservesLimits(int $fileBytes, int $originLimit, int $parameterBytes): void
    {
        $config = $this->multipartConfig();
        $config->requestFormat = 'multipart-create';
        $config->body = '{"acf":{"image":"{{image}}"},"title":"{{title}}"}';
        $snapshots = $this->snapshotsForSingleImage($fileBytes);
        foreach ($snapshots->getFileMap() as $file) {
            $handle = fopen($file['tmp_name'], 'c+b');
            ftruncate($handle, $fileBytes);
            fclose($handle);
        }
        $result = $this->createHandler($config, $snapshots, ['wpMaxUploadSize' => $originLimit])
            ->handle(['acf' => ['title' => str_repeat('x', $parameterBytes)]], new WP_REST_Request());
        self::assertFalse($result?->isOk());
        self::assertSame([], $this->requests);
        self::assertSame([], $snapshots->getFileMap());
    }

    public static function createLimitProvider(): array
    {
        return [
            'hard file limit' => [8 * 1024 * 1024 + 1, 64 * 1024 * 1024, 0],
            'lower origin limit' => [4, 3, 0],
            'parameter limit' => [4, 8 * 1024 * 1024, 1024 * 1024 + 1],
        ];
    }

    public function testMultipartCreatePreservesOptionalImageDestinations(): void
    {
        $config = $this->multipartConfig();
        $config->requestFormat = 'multipart-create';
        $config->body = '{"acf":{"image_a":"{{image_a.0}}","image_b":"{{image_b.0}}","image_c":"{{image_c.0}}"}}';
        $snapshots = $this->snapshotsForOptionalImages([
            'field_image_b' => "selected-b\x00\xff",
            'field_image_c' => "selected-c\r\n",
        ]);
        $result = $this->createHandler($config, $snapshots)->handle([], new WP_REST_Request());
        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);
        self::assertArrayNotHasKey('acf[image_a]', $parts);
        self::assertSame("selected-b\x00\xff", $this->referencedBinary($parts, 'acf[image_b]'));
        self::assertSame("selected-c\r\n", $this->referencedBinary($parts, 'acf[image_c]'));
    }

    public function testMultipartOmitsAnAbsentOptionalImageWithoutShiftingSelectedImages(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"image_a":"{{image_a.0}}","image_b":"{{image_b.0}}","image_c":"{{image_c.0}}"}}';

        $handler = $this->createHandler(
            $config,
            $this->snapshotsForOptionalImages([
                'field_image_b' => 'binary-content-for-image-b',
                'field_image_c' => 'binary-content-for-image-c',
            ])
        );

        $result = $handler->handle(
            [
                'acf' => [
                    'field_image_b' => [''],
                    'field_image_c' => [''],
                ],
            ],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        self::assertCount(1, $this->requests);

        $parts = $this->captureMultipartParts($this->requests[0]['args']);

        self::assertArrayNotHasKey('acf[image_a]', $parts);
        self::assertSame(
            'binary-content-for-image-b',
            $this->referencedBinary($parts, 'acf[image_b]')
        );
        self::assertSame(
            'binary-content-for-image-c',
            $this->referencedBinary($parts, 'acf[image_c]')
        );
    }

    /**
     * @dataProvider optionalImageSelectionProvider
     *
     * @param array<string, string> $selectedImages
     */
    public function testMultipartOptionalImageSelectionsKeepTheirDestinationFields(array $selectedImages): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"image_a":"{{image_a.0}}","image_b":"{{image_b.0}}","image_c":"{{image_c.0}}"}}';

        $handler = $this->createHandler($config, $this->snapshotsForOptionalImages($selectedImages));
        $result = $handler->handle(
            [
                'acf' => array_fill_keys(array_keys($selectedImages), ['']),
            ],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);

        foreach (['image_a', 'image_b', 'image_c'] as $image) {
            $field = 'acf[' . $image . ']';
            if (!array_key_exists('field_' . $image, $selectedImages)) {
                self::assertArrayNotHasKey($field, $parts);
                continue;
            }

            self::assertSame($selectedImages['field_' . $image], $this->referencedBinary($parts, $field));
        }
    }

    /**
     * @return array<string, array{0: array<string, string>}>
     */
    public static function optionalImageSelectionProvider(): array
    {
        return [
            'all empty' => [[]],
            'only first selected' => [[
                'field_image_a' => 'binary-content-for-image-a',
            ]],
            'only last selected' => [[
                'field_image_c' => 'binary-content-for-image-c',
            ]],
            'all selected' => [[
                'field_image_a' => 'binary-content-for-image-a',
                'field_image_b' => 'binary-content-for-image-b',
                'field_image_c' => 'binary-content-for-image-c',
            ]],
        ];
    }

    public function testMultipartKeepsAnExistingOptionalImageAttachmentId(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"image_a":"{{image_a.0}}","image_b":"{{image_b.0}}"}}';

        $handler = $this->createHandler(
            $config,
            $this->snapshotsForOptionalImages(['field_image_b' => 'binary-content-for-image-b'])
        );
        $result = $handler->handle(
            [
                'acf' => [
                    'field_image_a' => [123],
                    'field_image_b' => [''],
                ],
            ],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);

        self::assertSame('123', $parts['acf[image_a]'] ?? null);
        self::assertSame('binary-content-for-image-b', $this->referencedBinary($parts, 'acf[image_b]'));
    }

    public function testMultipartKeepsNonImageEmptyValuesFalseZeroAndLists(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"optional_image":"{{optional_image.0}}"},"empty_text":"{{text}}","false_value":"{{toggle}}","zero_value":"{{zero}}","items":"{{items}}"}';

        $handler = $this->createHandler($config, null);
        $result = $handler->handle(
            [
                'acf' => [
                    'field_optional_image' => [''],
                    'field_text' => '',
                    'field_toggle' => '0',
                    'field_zero' => 0,
                    'field_items' => [],
                ],
            ],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);

        self::assertArrayNotHasKey('acf[optional_image]', $parts);
        self::assertSame('', $parts['empty_text'] ?? null);
        self::assertSame('0', $parts['false_value'] ?? null);
        self::assertSame('0', $parts['zero_value'] ?? null);
        self::assertSame('items', $parts['_acf_rest_empty[]'] ?? null);
    }

    public function testMultipartKeepsAnExistingImageSubmittedByName(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"image_a":"{{image_a.0}}"}}';

        $result = $this->createHandler($config, null)->handle(
            ['acf' => ['image_a' => [123]]],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);
        self::assertSame('123', $parts['acf[image_a]'] ?? null);
    }

    public function testMultipartOmissionLeavesLiteralAndUnknownReferencesUnchanged(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"image_a":"{{image_a.0}}"},"literal":"","unknown":"{{unknown.0}}","text":"{{text}}","embedded":"before {{image_a.0}} after","null_image":"{{optional_image.0}}"}';

        $result = $this->createHandler($config, null)->handle(
            ['acf' => ['field_text' => '', 'field_optional_image' => [null]]],
            new WP_REST_Request()
        );

        self::assertTrue($result?->isOk());
        $parts = $this->captureMultipartParts($this->requests[0]['args']);
        self::assertArrayNotHasKey('acf[image_a]', $parts);
        self::assertSame('', $parts['literal'] ?? null);
        self::assertSame('', $parts['unknown'] ?? null);
        self::assertSame('', $parts['text'] ?? null);
        self::assertSame('before  after', $parts['embedded'] ?? null);
        self::assertSame('', $parts['null_image'] ?? null);
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

    public function testProtocolBudgetAppliesEvenWhenOriginAllowsLargerUploads(): void
    {
        $size = 9 * 1024 * 1024;
        $snapshots = $this->snapshotsForSingleImage($size);
        foreach ($snapshots->getFileMap() as $file) {
            $handle = fopen($file['tmp_name'], 'c+b');
            ftruncate($handle, $size);
            fclose($handle);
        }
        $handler = $this->createHandler($this->multipartConfig(), $snapshots, ['wpMaxUploadSize' => 64 * 1024 * 1024]);
        self::assertFalse($handler->handle(['acf' => ['image' => '999']], new WP_REST_Request())?->isOk());
        self::assertSame([], $this->requests);
    }

    public function testStructuredMultipartBudgetAppliesWithoutFiles(): void
    {
        $config = $this->multipartConfig();
        $config->body = '{"acf":{"description":"{{description}}"}}';
        $handler = $this->createHandler($config, null);
        $result = $handler->handle(['acf' => ['description' => str_repeat('x', 1024 * 1024 + 1)]], new WP_REST_Request());
        self::assertFalse($result?->isOk());
        self::assertSame([], $this->requests);
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

        $wpService->method('isWpError')->willReturnCallback(static fn($response): bool => $response instanceof WP_Error);
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
            static function ($key): array|false {
                $key = (string) $key;

                // ACF cannot resolve a name without a saved post field reference.
                if (!str_starts_with($key, 'field_')) {
                    return false;
                }

                return [
                    'key'  => $key,
                    'name' => str_replace('field_', '', $key),
                    'type' => $key === 'field_toggle'
                        ? 'true_false'
                        : (str_starts_with($key, 'field_image')
                            || $key === 'field_optional_image'
                                ? 'image'
                                : 'text'),
                ];
            }
        );

        $configMock = $this->createMock(ConfigInterface::class);
        $configMock->method('getFieldNamespace')->willReturn('acf');

        $moduleConfig = $this->createMock(ModuleConfigInterface::class);
        $moduleConfig->method('getWebHookHandlerConfig')->willReturn($config);
        $moduleConfig->method('getFieldKeysRegisteredAsFormFields')->willReturn([
            'field_image_a', 'field_image_b', 'field_image_c', 'field_optional_image',
        ]);

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

    /**
     * @param array<string, string> $files Field keys mapped to distinct binary contents.
     */
    private function snapshotsForOptionalImages(array $files): UploadedFileSnapshots
    {
        $params = [
            'name'     => [],
            'type'     => [],
            'tmp_name' => [],
            'error'    => [],
            'size'     => [],
        ];

        foreach ($files as $fieldKey => $contents) {
            $params['name'][$fieldKey] = [0 => $fieldKey . '.jpg'];
            $params['type'][$fieldKey] = [0 => 'image/jpeg'];
            $params['tmp_name'][$fieldKey] = [0 => $this->makeTempFile($contents)];
            $params['error'][$fieldKey] = [0 => UPLOAD_ERR_OK];
            $params['size'][$fieldKey] = [0 => strlen($contents)];
        }

        return new UploadedFileSnapshots($params, $this->createAcfServiceForSnapshotNames());
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

    /**
     * @param array<string, mixed> $requestArgs
     * @return array<string, string>
     */
    private function captureMultipartParts(array $requestArgs): array
    {
        $contentType = (string) ($requestArgs['headers']['Content-Type'] ?? '');
        preg_match('/boundary=(.+)$/', $contentType, $match);
        self::assertArrayHasKey(1, $match);

        $parts = [];
        foreach (explode('--' . $match[1], (string) $requestArgs['body']) as $part) {
            $segments = explode("\r\n\r\n", $part, 2);
            if (count($segments) !== 2 || !preg_match('/name="([^"]+)"/', $segments[0], $partMatch)) {
                continue;
            }

            $parts[$partMatch[1]] = substr($segments[1], 0, -2);
        }

        return $parts;
    }

    /**
     * @param array<string, string> $parts
     */
    private function referencedBinary(array $parts, string $field): string
    {
        $reference = $parts[$field] ?? null;
        self::assertIsString($reference);
        self::assertMatchesRegularExpression('/^\$file:[A-Za-z0-9_-]+$/', $reference);

        $filePart = '_acf_rest_files[' . substr($reference, strlen('$file:')) . ']';
        self::assertArrayHasKey($filePart, $parts);

        return $parts[$filePart];
    }
}
