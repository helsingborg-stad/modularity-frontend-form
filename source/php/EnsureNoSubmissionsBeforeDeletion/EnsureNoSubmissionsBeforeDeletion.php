<?php

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Hookable\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\DoAction;
use WpService\Contracts\GetPosts;

class EnsureNoSubmissionsBeforeDeletion implements Hookable {

    public function __construct(
        private ConfigInterface $config,
        private AddFilter&GetPosts&DoAction $wpService,
    )
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('pre_trash_post', [$this, 'preventTrashIfSubmissionsExist'], 10, 2);
        $this->wpService->addFilter('pre_delete_post', [$this, 'preventDeletionIfSubmissionsExist'], 10, 3);
    }

    public function preventTrashIfSubmissionsExist(mixed $trash, \WP_Post $post): mixed
    {
        $allowDeletion = $this->allowDeletion($post);

        if($allowDeletion === false) {
            $this->wpService->doAction(self::createHookName('trash_prevented'), $post);
        }

        return $allowDeletion ? $trash : true;
    }

    /**
     * Prevent deletion of a form module if there are existing submissions
     *
     * @param bool $delete Whether to delete the post
     * @param \WP_Post $post The post object
     * @param bool $forceDelete Whether to force delete
     * @return mixed Modified delete value. Will return true to prevent deletion.
     */
    public function preventDeletionIfSubmissionsExist($delete, \WP_Post $post, bool $forceDelete): mixed
    {
        $allowDeletion = $this->allowDeletion($post);

        if($allowDeletion === false) {
            $this->wpService->doAction(self::createHookName('deletion_prevented'), $post);
        }

        return $allowDeletion ? $delete : true;
    }

    private function allowDeletion(\WP_Post $post): bool 
    {
        if ($post->post_type !== $this->config->getModuleSlug()) {
            return true;
        }

        if($this->anyPostsAssociatedWithModule($post->ID)) {
            return false;
        }
    
        return true;
    }
        
        
    private function anyPostsAssociatedWithModule(int $postId): bool {
        $submissions = $this->wpService->getPosts([
            'post_type' => 'any',
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => $this->config->getMetaDataNamespace('module_id'),
                    'value' => $postId,
                    'compare' => '='
                ],
                [
                    'key' => $this->config->getMetaDataNamespace('submission'),
                    'value' => true,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1,
            'fields' => 'ids'
        ]);

        return count($submissions) > 0;
    }

    public static function createHookName(string $hookName): string
    {
        return self::class . "\\" . $hookName;
    }
}