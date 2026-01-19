<?php

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use PHPUnit\Framework\TestCase;
use WpService\Contracts\AddFilter;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetPosts;

class EnsureNoSubmissionsBeforeDeletionTest extends TestCase {
    /**
     * @testdox preventDeletionIfSubmissionsExist() returns provided $delete value for non-form module post types
     */
    public function testPreventDeletionIfSubmissionsExist_NonFormModulePostType_ReturnsDeleteValue(): void {
        $modulePostType = 'mod-frontend-form';
        $delete = 'test_delete_value';
        $post = new \WP_Post([]);
        $post->post_type = 'post';
        $forceDelete = false;
        $config = $this->createMock(\ModularityFrontendForm\Config\ConfigInterface::class);
        $config->method('getModuleSlug')->willReturn($modulePostType);

        $ensureNoSubmissionsBeforeDeletion = new EnsureNoSubmissionsBeforeDeletion($config, $this->createWpService());
        $result = $ensureNoSubmissionsBeforeDeletion->preventDeletionIfSubmissionsExist($delete, $post, $forceDelete);

        static::assertSame($delete, $result);
    }

    /**
     * @testdox preventDeletionIfSubmissionsExist() returns false to prevent deletion when submissions exist
     */
    public function testPreventDeletionIfSubmissionsExist_SubmissionsExist_ReturnsTrue(): void {
        $modulePostType = 'mod-frontend-form';
        $delete = false;
        $post = new \WP_Post([]);
        $post->ID = 123;
        $post->post_type = $modulePostType;
        $forceDelete = false;
        $config = $this->createMock(\ModularityFrontendForm\Config\ConfigInterface::class);
        $config->method('getModuleSlug')->willReturn($modulePostType);
        $wpService = $this->createWpService([1]);

        $ensureNoSubmissionsBeforeDeletion = new EnsureNoSubmissionsBeforeDeletion($config, $wpService);
        $result = $ensureNoSubmissionsBeforeDeletion->preventDeletionIfSubmissionsExist($delete, $post, $forceDelete);

        static::assertFalse($result);
    }

    /**
     * @testdox triggers action 'deletion_prevented' when deletion is prevented due to existing submissions
     */
    public function testPreventDeletionIfSubmissionsExist_SubmissionsExist_TriggersDeletionPreventedAction(): void {
        $modulePostType = 'mod-frontend-form';
        $delete = false;
        $post = new \WP_Post([]);
        $post->ID = 123;
        $post->post_type = $modulePostType;
        $forceDelete = false;
        $config = $this->createMock(\ModularityFrontendForm\Config\ConfigInterface::class);
        $config->method('getModuleSlug')->willReturn($modulePostType);
        $wpService = $this->createWpService([ 'getPosts' => [ (object) ['ID' => 1] ] ]);

        $ensureNoSubmissionsBeforeDeletion = new EnsureNoSubmissionsBeforeDeletion($config, $wpService);
        $ensureNoSubmissionsBeforeDeletion->preventDeletionIfSubmissionsExist($delete, $post, $forceDelete);

        static::assertArrayHasKey('doAction', $wpService->calls);
        static::assertSame('ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion\EnsureNoSubmissionsBeforeDeletion\deletion_prevented', $wpService->calls['doAction'][0]);
        static::assertSame($post, $wpService->calls['doAction'][1]);
    }

    /**
     * @testdox triggers action 'trash_prevented' when deletion is prevented due to existing submissions
     */
    public function testPreventTrashIfSubmissionsExist_SubmissionsExist_TriggersTrashPreventedAction(): void {
        $modulePostType = 'mod-frontend-form';
        $trash = false;
        $post = new \WP_Post([]);
        $post->ID = 123;
        $post->post_type = $modulePostType;
        $config = $this->createMock(\ModularityFrontendForm\Config\ConfigInterface::class);
        $config->method('getModuleSlug')->willReturn($modulePostType);
        $wpService = $this->createWpService([ 'getPosts' => [ (object) ['ID' => 1] ] ]);

        $ensureNoSubmissionsBeforeDeletion = new EnsureNoSubmissionsBeforeDeletion($config, $wpService);
        $ensureNoSubmissionsBeforeDeletion->preventTrashIfSubmissionsExist($trash, $post);

        static::assertArrayHasKey('doAction', $wpService->calls);
        static::assertSame('ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion\EnsureNoSubmissionsBeforeDeletion\trash_prevented', $wpService->calls['doAction'][0]);
        static::assertSame($post, $wpService->calls['doAction'][1]);
    }

    private function createWpService(array $data = []): AddFilter&GetPosts&DoAction {
        return new class ($data) implements AddFilter, GetPosts, DoAction {
            public $calls = [];
            public function __construct(private array $data) {}

            public function addFilter(string $hookName, callable $callback, int $priority = 10, int $acceptedArgs = 1): true
            {
                return true;
            }

            public function getPosts(?array $args = null): array
            {
                return $this->data['getPosts'] ?? [];
            }

            public function doAction(string $hookName, mixed ...$arg): void
            {
                $this->calls[__FUNCTION__] = func_get_args();
                return;
            }
        };
    }
}