<?php

declare(strict_types=1);

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\NullConfig;
use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use WpService\WpService;

class AdminNoticesTest extends TestCase {

    private static function createWpService(): WpService {
        return new FakeWpService();
    }

    private static function createConfig(): ConfigInterface {
        return new class extends NullConfig implements ConfigInterface {
            public function getModuleSlug(): string {
                return 'test-module';
            }
        };
    }

    /**
     * @testdox modifyRedirectForTrashedPost adds custom param and removes trashed
     */
    public function test_modifyRedirectForTrashedPost_addsCustomParamAndRemovesTrashed() {
        $config = self::createConfig();
        $wpService = new class extends FakeWpService {
            public function removeQueryArg($key, $query = false): string {
                if ($key === 'trashed' && $query === 'http://example.com/?trashed=1') {
                    return 'http://example.com/';
                }
                return parent::removeQueryArg($key, $query);
            }
            public function addQueryArg(...$args): string {
                if ($args === ['test-module-trash-prevented', 123, 'http://example.com/']) {
                    return 'http://example.com/?test-module-trash-prevented=123';
                }
                return parent::addQueryArg(...$args);
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = new \WP_Post([]);
        $post->ID = 123;
        $result = $sut->modifyRedirectForTrashedPost('http://example.com/?trashed=1', $post);
        static::assertEquals('http://example.com/?test-module-trash-prevented=123', $result);
    }

    /**
     * @testdox modifyRedirectForTrashedPost returns unchanged if no trashed
     */
    public function test_modifyRedirectForTrashedPost_returnsUnchangedIfNoTrashed() {
        $config = self::createConfig();
        $wpService = static::createWpService();
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = new \WP_Post([]);
        $post->ID = 123;
        $result = $sut->modifyRedirectForTrashedPost('http://example.com/', $post);
        static::assertEquals('http://example.com/', $result);
    }

    /**
     * @testdox modifyRedirectForDeletedPost adds custom param and removes deleted
     */
    public function test_modifyRedirectForDeletedPost_addsCustomParamAndRemovesDeleted() {
        $config = self::createConfig();
        $wpService = new class extends FakeWpService {
            public function removeQueryArg($key, $query = false): string {
                if ($key === 'deleted' && $query === 'http://example.com/?deleted=1') {
                    return 'http://example.com/';
                }
                return parent::removeQueryArg($key, $query);
            }
            public function addQueryArg(...$args): string {
                if ($args === ['test-module-deletion-prevented', 456, 'http://example.com/']) {
                    return 'http://example.com/?test-module-deletion-prevented=456';
                }
                return parent::addQueryArg(...$args);
            }
        };
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = new \WP_Post([]);
        $post->ID = 456;
        $result = $sut->modifyRedirectForDeletedPost('http://example.com/?deleted=1', $post);
        static::assertEquals('http://example.com/?test-module-deletion-prevented=456', $result);
    }

    /**
     * @testdox modifyRedirectForDeletedPost returns unchanged if no deleted
     */
    public function test_modifyRedirectForDeletedPost_returnsUnchangedIfNoDeleted() {
        $config = self::createConfig();
        $wpService = static::createWpService();
        $sut = new AlterTrashedRedirectToAllowCustomNotice($config, $wpService);
        $post = new \WP_Post([]);
        $post->ID = 456;
        $result = $sut->modifyRedirectForDeletedPost('http://example.com/', $post);
        static::assertEquals('http://example.com/', $result);
    }
}
