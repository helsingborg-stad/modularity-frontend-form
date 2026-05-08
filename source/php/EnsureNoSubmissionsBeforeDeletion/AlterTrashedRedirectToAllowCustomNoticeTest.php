<?php

declare(strict_types=1);

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\NullConfig;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use WpService\WpService;

class AlterTrashedRedirectToAllowCustomNoticeTest extends TestCase {
    
    /**
     * @testdox Adds filter for wp_redirect on trash_prevented hook
     */
    public function testAddsHookOnTrashPrevented(): void {
        $config = self::createConfig();
        $wpService = self::createWpService();

        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $sut->addHooks();

        static::assertCount(2, $wpService->methodCalls['addAction']);
    }

    /**
     * @testdox Calls addFilter for wp_redirect when trash_prevented is triggered
     */
    public function testAlterTrashedRedirectAddsFilter(): void {
        $config = self::createConfig();
        $wpService = new class extends \WpService\Implementations\FakeWpService {
            public array $addFilterCalls = [];
            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true {
                $this->addFilterCalls[] = [$hookName, $callback, $priority, $acceptedArgs];
                return true;
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(123);
        $sut->alterTrashedRedirect($post);
        static::assertNotEmpty($wpService->addFilterCalls);
        static::assertSame('wp_redirect', $wpService->addFilterCalls[0][0]);
    }

    /**
     * @testdox Calls addFilter for wp_redirect when deletion_prevented is triggered
     */
    public function testAlterDeletedRedirectAddsFilter(): void {
        $config = self::createConfig();
        $wpService = new class extends \WpService\Implementations\FakeWpService {
            public array $addFilterCalls = [];
            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true {
                $this->addFilterCalls[] = [$hookName, $callback, $priority, $acceptedArgs];
                return true;
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(456);
        $sut->alterDeletedRedirect($post);
        static::assertNotEmpty($wpService->addFilterCalls);
        static::assertSame('wp_redirect', $wpService->addFilterCalls[0][0]);
    }

    /**
     * @testdox Modifies redirect URL for trashed post by removing 'trashed' and adding custom parameter
     */
    public function testModifyRedirectForTrashedPostModifiesUrl(): void {
        $config = self::createConfig();
        $wpService = new class extends \WpService\Implementations\FakeWpService {
            public function removeQueryArg(...$args): string {
                // expects: key, location
                $key = $args[0] ?? '';
                $location = $args[1] ?? '';
                return str_replace($key . '=1', '', $location);
            }
            public function addQueryArg(...$args): string {
                // expects: key, value, location
                $key = $args[0] ?? '';
                $value = $args[1] ?? '';
                $location = $args[2] ?? '';
                return $location . (str_contains($location, '?') ? '&' : '?') . $key . '=' . $value;
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(99);
        $original = 'http://example.com/?trashed=1';
        $result = $sut->modifyRedirectForTrashedPost($original, $post);
        static::assertStringNotContainsString('trashed=', $result);
        static::assertStringContainsString('test-module-trash-prevented=99', $result);
    }

    /**
     * @testdox Leaves redirect URL unchanged if 'trashed' is not present
     */
    public function testModifyRedirectForTrashedPostNoChangeIfNoTrashed(): void {
        $config = self::createConfig();
        $wpService = new \WpService\Implementations\FakeWpService();
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(1);
        $original = 'http://example.com/?foo=bar';
        $result = $sut->modifyRedirectForTrashedPost($original, $post);
        static::assertSame($original, $result);
    }

    /**
     * @testdox Modifies redirect URL for deleted post by removing 'deleted' and adding custom parameter
     */
    public function testModifyRedirectForDeletedPostModifiesUrl(): void {
        $config = self::createConfig();
        $wpService = new class extends \WpService\Implementations\FakeWpService {
            public function removeQueryArg(...$args): string {
                $key = $args[0] ?? '';
                $location = $args[1] ?? '';
                return str_replace($key . '=1', '', $location);
            }
            public function addQueryArg(...$args): string {
                $key = $args[0] ?? '';
                $value = $args[1] ?? '';
                $location = $args[2] ?? '';
                return $location . (str_contains($location, '?') ? '&' : '?') . $key . '=' . $value;
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(77);
        $original = 'http://example.com/?deleted=1';
        $result = $sut->modifyRedirectForDeletedPost($original, $post);
        static::assertStringNotContainsString('deleted=', $result);
        static::assertStringContainsString('test-module-deletion-prevented=77', $result);
    }

    /**
     * @testdox Leaves redirect URL unchanged if 'deleted' is not present
     */
    public function testModifyRedirectForDeletedPostNoChangeIfNoDeleted(): void {
        $config = self::createConfig();
        $wpService = new \WpService\Implementations\FakeWpService();
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = self::makePost(2);
        $original = 'http://example.com/?foo=bar';
        $result = $sut->modifyRedirectForDeletedPost($original, $post);
        static::assertSame($original, $result);
    }

    private static function makePost($id): \WP_Post {
        $post = new \WP_Post([]);
        $post->ID = $id;
        return $post;
    }

    private static function createConfig(): ConfigInterface {
        return new class extends NullConfig implements ConfigInterface {
            public function getModuleSlug(): string {
                return 'test-module';
            }
        };
    }

    private static function createWpService(): WpService {
        return new FakeWpService([
            'addAction' => true
        ]);
    }
}
