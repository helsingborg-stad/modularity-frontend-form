<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use Psr\Log\LoggerInterface;
use PsrLogger\Contracts\LoggerFactoryInterface;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

interface SnapshotTestLogger extends LoggerFactoryInterface, LoggerInterface {}

require_once dirname(__DIR__, 3) . '/tests/HandlerTestCase.php';

final class HandlerFactoryTest extends HandlerTestCase
{
    /** @dataProvider formatProvider */
    public function testSnapshotsSurviveDatabaseConsumptionOnlyForMultipart(string $format, ?string $version, mixed $invalid = false, bool $skip = false): void
    {
        static $moduleId = 5000;
        $id = ++$moduleId;
        $original = tempnam(sys_get_temp_dir(), 'sender-factory-');
        self::assertIsString($original);
        file_put_contents($original, "selected-image\x00\xff");
        $before = glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [];
        try {
            $request = $this->createMock(WP_REST_Request::class);
            $uploads = [
                'name' => ['field_image' => ['selected.png']],
                'type' => ['field_image' => ['image/png']],
                'tmp_name' => ['field_image' => [$original]],
                'error' => ['field_image' => [UPLOAD_ERR_OK]],
                'size' => ['field_image' => [16]],
            ];
            foreach ($uploads as &$values) {
                $values['field_other'] = $values['field_image'];
            }
            unset($values);
            $request->method('get_file_params')->willReturn(['acf' => $invalid ? [] : $uploads]);
            $config = $this->createMock(Config::class);
            $config->method('getFieldNamespace')->willReturn('acf');
            $config->method('getUnprintableKeys')->willReturn(['module-id', 'holding-post-id']);
            $module = $this->createMock(ModuleConfigInterface::class);
            $module->method('getModuleId')->willReturn($id);
            $module->method('getFieldKeysRegisteredAsFormFields')->willReturn(['field_image', 'field_other']);
            $module->method('getActivatedHandlers')->willReturn($skip ? ['WebHookHandler'] : ['WpDbHandler', 'MailHandler', 'WebHookHandler']);
            $module->method('getMailHandlerConfig')->willReturn((object) ['Recivers' => [['Email' => 'sink@example.test']]]);
            $module->method('getWpDbHandlerConfig')->willReturn((object) [
                'saveToPostType' => 'submission', 'saveToPostTypeStatus' => 'draft',
            ]);
            $module->method('getWebHookHandlerConfig')->willReturn((object) [
                'requestFormat' => $format,
                'callbackUrl' => 'https://destination.test/create',
                'body' => $invalid ?: '{"acf":{"image":"{{image}}"}}',
            ]);
            $moduleFactory = $this->createMock(ModuleConfigFactoryInterface::class);
            $moduleFactory->method('create')->willReturn($module);
            $logger = $this->createMock(SnapshotTestLogger::class);
            $logger->method('createLogger')->willReturn($logger);
            $acf = $this->createMock(AcfService::class);
            $acf->method('getFieldObject')->willReturnCallback(static fn($key) => [
                'key' => $key, 'name' => substr($key, 6), 'type' => $key === 'field_image' ? 'image' : 'file',
            ]);
            $wp = new FakeWpService([
                'wpInsertPost' => 123, 'wpMail' => true, '__' => static fn($text) => $text,
                'sanitizeTextField' => static fn($text) => $text, 'wpGeneratePassword' => 'test-password',
                'isWpError' => false, 'wpMaxUploadSize' => 8388608, 'wpRemoteRetrieveResponseCode' => 201,
                'wpRemotePost' => static function ($url, $args) use ($version): array {
                    if ($version === null) {
                        self::assertSame('application/json', $args['headers']['Content-Type']);
                        self::assertSame(['acf' => ['image' => '']], json_decode($args['body'], true));
                    } else {
                        self::assertSame($version, $args['headers']['X-ACF-Rest-Upload-Version']);
                        self::assertStringContainsString('name="_acf_rest_payload"', $args['body']);
                        self::assertStringContainsString('{"acf":{"image":"$file:file_0"}}', $args['body']);
                        self::assertStringContainsString("selected-image\x00\xff", $args['body']);
                    }
                    return ['response' => ['code' => 201]];
                },
            ]);
            $params = (object) ['moduleId' => $id];
            $data = ['module-id' => $id, 'holding-post-id' => 1];
            $handlers = (new HandlerFactory($wp, $acf, $config, $moduleFactory, $logger))->createHandlers($params, $request);
            self::assertCount($skip ? 1 : 3, $handlers);
            $snapshots = array_values(array_diff(glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [], $before));
            self::assertCount($version === null || $invalid ? 0 : 1, $snapshots);
            if ($skip) {
                // Runs after the owner's shutdown fallback; failure makes the child process fail.
                register_shutdown_function(static function () use ($snapshots, $original): void {
                    foreach ($snapshots as $path) {
                        self::assertFileDoesNotExist($path);
                    }
                    self::assertSame("selected-image\x00\xff", file_get_contents($original));
                    unlink($original);
                });
                return;
            }
            self::assertTrue($handlers[1]->handle($data, $request)?->isOk());
            self::assertCount(1, $wp->methodCalls['wpMail']);
            if ($invalid) {
                self::assertTrue($handlers[0]->handle($data, $request)?->isOk());
                self::assertFalse($handlers[2]->handle($data, $request)?->isOk());
                self::assertCount(1, $wp->methodCalls['wpInsertPost']);
                self::assertArrayNotHasKey('wpRemotePost', $wp->methodCalls);
                self::assertFileExists($original);
                return;
            }

            // Exercise Database handling with a consuming file-handler boundary.
            $files = $this->createMock(FileHandlerInterface::class);
            $files->expects(self::once())->method('handle')->willReturnCallback(
                static function () use ($original): array {
                    unlink($original);
                    return ['field_image' => [['id' => 456]]];
                }
            );
            $database = new WpDbHandler($wp, $acf, $config, $module, $params, fileHandler: $files);
            self::assertTrue($database->handle([], $request)?->isOk());
            self::assertFileDoesNotExist($original);
            foreach ($snapshots as $path) {
                self::assertSame("selected-image\x00\xff", file_get_contents($path));
            }
            self::assertTrue($handlers[2]->handle(['module-id' => $id, 'holding-post-id' => 1, 'acf' => []], $request)?->isOk());
            self::assertCount(1, $wp->methodCalls['wpRemotePost']);
            foreach ($snapshots as $path) {
                self::assertFileDoesNotExist($path);
            }
        } finally {
            if (!$skip && is_file($original)) {
                unlink($original);
            }
        }
    }

    public static function formatProvider(): array
    {
        return [
            'Database consumption' => ['multipart-json', '3'],
            'ordinary JSON' => ['json', null],
            'independent handlers after mapping failure' => ['multipart-json', '3', '{"image":["{{image}}"]}'],
            'independent handlers after malformed configuration' => ['multipart-json', '3', ['invalid-template']],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSkippedWebhookCleansAtShutdown(): void
    {
        $this->testSnapshotsSurviveDatabaseConsumptionOnlyForMultipart('multipart-json', '3', false, true);
    }
}
