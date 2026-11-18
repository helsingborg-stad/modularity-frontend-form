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
    private array $sourcePaths = [];

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
        $single = $this->createSourceFile('single');
        $first = $this->createSourceFile('first');
        $second = $this->createSourceFile('second');
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
                'field_single' => $single,
                'field_multiple' => [$first, $second],
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
        $contents = [
            'single.pdf' => 'single',
            'first.pdf' => 'first',
            'second.pdf' => 'second',
        ];
        $wp = new FakeWpService([
            'wpTempnam' => static function (): string {
                $path = tempnam(sys_get_temp_dir(), 'mff-sideload-');
                $withExtension = $path . '.tmp';
                rename($path, $withExtension);
                return $withExtension;
            },
            'mediaHandleSideload' => static function (array $file) use ($contents): int {
                static $id = 100;
                self::assertSame(
                    ['name', 'type', 'tmp_name', 'error', 'size'],
                    array_keys($file),
                );
                self::assertStringEndsWith('.tmp', $file['tmp_name']);
                self::assertSame($contents[$file['name']], file_get_contents($file['tmp_name']));
                return ++$id;
            },
            'isWpError' => false,
            '__' => static fn(string $text): string => $text,
        ]);

        $result = (new WpDbFileHandler($config, $module, $wp))->handle($request, 99);

        self::assertCount(3, $wp->methodCalls['mediaHandleSideload']);
        self::assertSame([99, 99, 99], array_column($wp->methodCalls['mediaHandleSideload'], 1));
        self::assertFileDoesNotExist($wp->methodCalls['mediaHandleSideload'][0][0]['tmp_name']);
        self::assertFileDoesNotExist($wp->methodCalls['mediaHandleSideload'][2][0]['tmp_name']);
        self::assertSame([102, 103], array_column($result['field_multiple'], 'id'));
        self::assertArrayNotHasKey('field_optional', $result);
    }

    protected function tearDown(): void
    {
        foreach ($this->sourcePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function createSourceFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'mff-upload-');
        file_put_contents($path, $contents);
        $this->sourcePaths[] = $path;

        return $path;
    }
}
