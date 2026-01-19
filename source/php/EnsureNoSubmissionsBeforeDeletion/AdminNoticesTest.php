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
     * @testdox Adds hooks for query_vars and admin_notices
     */
    public function testAddHooksAddsFilterAndAction(): void
    {
        $wpService = new FakeWpService([
            'addFilter' => true,
            'addAction' => true
        ]);
        $sut = new AdminNotices(self::createConfig(), $wpService);
        $sut->addHooks();

        $this->assertArrayHasKey('addFilter', $wpService->methodCalls);
        $this->assertArrayHasKey('addAction', $wpService->methodCalls);
    }

    /**
     * @testdox registerQueryVar adds both trashed and deleted query vars
     */
    public function testRegisterQueryVarAddsVars(): void
    {
        $sut = new AdminNotices(self::createConfig(), new FakeWpService());
        $vars = ['foo'];
        $result = $sut->registerQueryVar($vars);

        $this->assertContains('test-module-trash-prevented', $result);
        $this->assertContains('test-module-deletion-prevented', $result);
    }

    /**
     * @testdox adminNotices adds trash prevented notice if trashed query var is present
     */
    public function testAdminNoticesAddsTrashPreventedNotice(): void
    {
        $wpService = new FakeWpService([
            'getQueryVar' => function($key) { return $key === 'test-module-trash-prevented' ? 123 : null; },
            '__' => function($msg, $domain = null) { return $msg; },
            'wpAdminNotice' => function($msg, $opts = []) { return null; }
        ]);
        $sut = new AdminNotices(self::createConfig(), $wpService);
        $sut->adminNotices();

        $calls = $wpService->methodCalls['wpAdminNotice'] ?? [];
        $this->assertNotEmpty($calls);
        $this->assertStringContainsString('Cannot move form module to trash', $calls[0][0]);
    }

    /**
     * @testdox adminNotices adds deletion prevented notice if deleted query var is present
     */
    public function testAdminNoticesAddsDeletionPreventedNotice(): void
    {
        $wpService = new FakeWpService([
            'getQueryVar' => function($key) { return $key === 'test-module-deletion-prevented' ? 1 : null; },
            '__' => function($msg, $domain = null) { return $msg; },
            'wpAdminNotice' => function($msg, $opts = []) { return null; }
        ]);
        $sut = new AdminNotices(self::createConfig(), $wpService);
        $sut->adminNotices();

        $calls = $wpService->methodCalls['wpAdminNotice'] ?? [];
        $this->assertNotEmpty($calls);
        $this->assertStringContainsString('Cannot delete form module', $calls[0][0]);
    }

    /**
     * @testdox addDeletionsPreventedNotice prints correct notice
     */
    public function testAddDeletionsPreventedNotice(): void
    {
        $wpService = new FakeWpService([
            '__' => function($msg, $domain = null) { return $msg; },
            'wpAdminNotice' => function($msg, $opts = []) { return null; }
        ]);
        $sut = new AdminNotices(self::createConfig(), $wpService);
        $sut->addDeletionsPreventedNotice();

        $calls = $wpService->methodCalls['wpAdminNotice'] ?? [];
        $this->assertNotEmpty($calls);
        $this->assertStringContainsString('Cannot delete form module', $calls[0][0]);
    }

    /**
     * @testdox addTrashPreventedNotice prints correct notice
     */
    public function testAddTrashPreventedNotice(): void
    {
        $wpService = new FakeWpService([
            '__' => function($msg, $domain = null) { return $msg; },
            'wpAdminNotice' => function($msg, $opts = []) { return null; }
        ]);
        $sut = new AdminNotices(self::createConfig(), $wpService);
        $sut->addTrashPreventedNotice();

        $calls = $wpService->methodCalls['wpAdminNotice'] ?? [];
        $this->assertNotEmpty($calls);
        $this->assertStringContainsString('Cannot move form module to trash', $calls[0][0]);
    }
}
