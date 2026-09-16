<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PsrLogger\Contracts\LoggerFactoryInterface;
use WP_REST_Request;
use WpService\WpService;

interface SnapshotTestLogger extends LoggerFactoryInterface, LoggerInterface {}

final class HandlerFactoryTest extends TestCase
{
    /** @dataProvider formatProvider */
    public function testSnapshotsSurviveDatabaseConsumptionOnlyForMultipart(string $format, ?string $version): void
    {
        static $moduleId = 5000;
        $id = ++$moduleId;
        $original = tempnam(sys_get_temp_dir(), 'sender-factory-');
        self::assertIsString($original);
        file_put_contents($original, "selected-image\x00\xff");
        $before = glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [];
        try {
            $request = $this->createMock(WP_REST_Request::class);
            $request->method('get_file_params')->willReturn(['acf' => [
                'name' => ['field_image' => ['selected.png']],
                'type' => ['field_image' => ['image/png']],
                'tmp_name' => ['field_image' => [$original]],
                'error' => ['field_image' => [UPLOAD_ERR_OK]],
                'size' => ['field_image' => [16]],
            ]]);
            $config = $this->createMock(Config::class);
            $config->method('getFieldNamespace')->willReturn('acf');
            $module = $this->createMock(ModuleConfigInterface::class);
            $module->method('getModuleId')->willReturn($id);
            $module->method('getFieldKeysRegisteredAsFormFields')->willReturn(['field_image']);
            $module->method('getActivatedHandlers')->willReturn(['WpDbHandler', 'WebHookHandler']);
            $module->method('getWpDbHandlerConfig')->willReturn((object) [
                'saveToPostType' => 'submission', 'saveToPostTypeStatus' => 'draft',
            ]);
            $module->method('getWebHookHandlerConfig')->willReturn((object) [
                'requestFormat' => $format,
                'callbackUrl' => 'https://destination.test/create',
                'body' => '{"acf":{"image":"{{image}}"}}',
            ]);
            $moduleFactory = $this->createMock(ModuleConfigFactoryInterface::class);
            $moduleFactory->method('create')->willReturn($module);
            $logger = $this->createMock(SnapshotTestLogger::class);
            $logger->method('createLogger')->willReturn($logger);
            $acf = $this->createMock(AcfService::class);
            $acf->method('getFieldObject')->willReturn(['key' => 'field_image', 'name' => 'image', 'type' => 'image']);
            $wp = $this->createMock(WpService::class);
            $wp->method('wpInsertPost')->willReturn(123);
            $wp->method('isWpError')->willReturn(false);
            $wp->method('wpMaxUploadSize')->willReturn(8 * 1024 * 1024);
            $wp->method('wpRemoteRetrieveResponseCode')->willReturn(201);
            $wp->expects(self::once())->method('wpRemotePost')->willReturnCallback(
                static function ($url, $args) use ($version): array {
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
                }
            );
            $params = (object) ['moduleId' => $id];
            $handlers = (new HandlerFactory($wp, $acf, $config, $moduleFactory, $logger))->createHandlers($params, $request);
            self::assertCount(2, $handlers);
            $snapshots = array_values(array_diff(glob(sys_get_temp_dir() . '/mff-webhook-*') ?: [], $before));
            self::assertCount($version === null ? 0 : 1, $snapshots);

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
            self::assertTrue($handlers[1]->handle(['module-id' => $id, 'holding-post-id' => 1, 'acf' => []], $request)?->isOk());
            foreach ($snapshots as $path) {
                self::assertFileDoesNotExist($path);
            }
        } finally {
            if (is_file($original)) {
                unlink($original);
            }
        }
    }

    public static function formatProvider(): array
    {
        return [['multipart-json', '3'], ['json', null]];
    }
}
