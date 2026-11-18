<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use WpService\Implementations\FakeWpService;

final class WpDbFileHandlerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (defined('ABSPATH')) {
            return;
        }

        $wordpressIncludes = sys_get_temp_dir() . '/mff-wordpress-includes/wp-admin/includes';
        mkdir($wordpressIncludes, 0777, true);
        foreach (['file.php', 'media.php', 'image.php'] as $file) {
            file_put_contents($wordpressIncludes . '/' . $file, "<?php\n");
        }
        define('ABSPATH', dirname(dirname($wordpressIncludes)) . '/');
    }

    public function testPassesCanonicalWordPressFileArraysToSideload(): void
    {
        $request = $this->createMock(WP_REST_Request::class);
        $request->method('get_file_params')->willReturn(['acf' => [
            'name' => [
                'field_single' => 'single.pdf',
                'field_multiple' => ['first.pdf', 'second.pdf'],
                'field_optional' => [''],
            ],
            'type' => [
                'field_single' => 'application/pdf',
                'field_multiple' => ['application/pdf', 'application/pdf'],
                'field_optional' => [''],
            ],
            'tmp_name' => [
                'field_single' => '/tmp/php-single',
                'field_multiple' => ['/tmp/php-first', '/tmp/php-second'],
                'field_optional' => [''],
            ],
            'error' => [
                'field_single' => UPLOAD_ERR_OK,
                'field_multiple' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'field_optional' => [UPLOAD_ERR_NO_FILE],
            ],
            'size' => [
                'field_single' => 11,
                'field_multiple' => [12, 13],
                'field_optional' => [0],
            ],
        ]]);
        $config = $this->createMock(Config::class);
        $config->method('getFieldNamespace')->willReturn('acf');
        $module = $this->createMock(ModuleConfigInterface::class);
        $wp = new FakeWpService([
            'mediaHandleSideload' => static function (array $file): int {
                static $id = 100;
                self::assertSame(
                    ['name', 'type', 'tmp_name', 'error', 'size'],
                    array_keys($file),
                );
                self::assertNotSame('', $file['tmp_name']);
                return ++$id;
            },
            'isWpError' => false,
            '__' => static fn(string $text): string => $text,
        ]);

        $result = (new WpDbFileHandler($config, $module, $wp))->handle($request, 99);

        self::assertCount(3, $wp->methodCalls['mediaHandleSideload']);
        self::assertSame([99, 99, 99], array_column($wp->methodCalls['mediaHandleSideload'], 1));
        self::assertSame('/tmp/php-single', $wp->methodCalls['mediaHandleSideload'][0][0]['tmp_name']);
        self::assertSame('/tmp/php-second', $wp->methodCalls['mediaHandleSideload'][2][0]['tmp_name']);
        self::assertSame([102, 103], array_column($result['field_multiple'], 'id'));
        self::assertArrayNotHasKey('field_optional', $result);
    }
}
