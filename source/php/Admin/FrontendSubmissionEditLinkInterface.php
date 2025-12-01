<?php

namespace ModularityFrontendForm\Admin;

use ModularityFrontendForm\Config\ConfigInterface;
use WpService\WpService;
use Modularity\Helper\ModuleUsageById;

/**
 * Class EditSubmissionOnFrontendInterface
 * 
 * This class manages the ability to edit frontend submissions, 
 * by clicking a link in the admin interface.
 * 
 * @package ModularityFrontendForm\Admin
 */
class FrontendSubmissionEditLinkInterface implements \Municipio\HooksRegistrar\Hookable
{
    private bool $isEnabled = true;

    public function __construct(private ConfigInterface $config, private WpService $wpService)
    {
    }

    /**
     * Add hooks to WordPress
     * @return void
     */
    public function addHooks(): void
    {
        if (!$this->isEnabled) {
            return;
        }

        $this->wpService->addAction('admin_notices', function () {
            $postId             = $this->wpService->getTheId();
            $postWithFormModule = $this->getPostWithFormModule($postId);
            $this->maybeShowFrontendNotice(
                $postId,
                $this->constructFrontendUrl($postId, $postWithFormModule)
            );
        });
    }

    /**
     * Constructs the frontend URL with token and postId query parameters.
     *
     * @param int $submissionPostId             The ID of the submission post
     * @param object|null $postWithFormModule   The post object containing the form module
     * @return string
     */
    private function constructFrontendUrl(int $submissionPostId, $postWithFormModule): string
    {
        // Get the token from the post password
        $token = $this->getPostPasswordToken($submissionPostId);
        
        // Get the most appropriate URL to the post with the form module
        $url = $this->wpService->getPermalink(
            $postWithFormModule->post_id ?? $submissionPostId
        );

        // Append token and postId as query parameters
        $url = add_query_arg('token', $token, $url);
        $url = add_query_arg('postId', $submissionPostId, $url);

        return $url;
    }

    /**
     * Gets the post password token for a given post.
     * @param int $postId
     * @return string
     */
    private function getPostPasswordToken(int $postId): string
    {
        return $this->wpService->getPost($postId)->post_password ?? '';
    }

    /**
     * Checks if the submission meta value is true for a given post.
     * This is a indication that the post was created via frontend submission.
     *
     * @param int $postId
     * @return bool
     */
    public function isSubmittedByForm(int $postId): bool
    {
        return filter_var(
            $this->wpService->getPostMeta(
                $postId, 
                $this->config->getMetaDataNamespace('submission'), 
                true
            )
        , FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Shows an admin notice with a link to the frontend UI if submission meta is true.
     *
     * @param int $postId
     * @param string $frontendUrl
     */
    public function maybeShowFrontendNotice(int $postId, string $frontendUrl): void
    {
        if ($this->isSubmittedByForm($postId)) {
            printf(
                '<div class="notice notice-info"><p>%s <a href="%s" target="_blank">%s</a></p></div>',
                esc_html__('Edit as end user', 'modularity-frontend-form'),
                esc_url($frontendUrl),
                esc_html__('View on frontend', 'modularity-frontend-form')
            );
        }
    }

    /**
     * Get the module ID associated with a post.
     */
    private function getModuleIdForPost(int $postId): ?int
    {
        $postModuleId = $this->config->getMetaDataNamespace('module_id');
        $moduleId = $this->wpService->getPostMeta($postId, $postModuleId, true);
        return $moduleId ?: null;
    }

    /**
     * Get all pages that use a given module ID.
     */
    private function getPagesWithModule(int $moduleId): array
    {
        $moduleUsageById = new ModuleUsageById();
        return $moduleUsageById->getModuleUsageById($moduleId) ?: [];
    }

    /**
     * Find a published page from a list of pages, or return the first private page if none are published.
     */
    private function findPublishedOrPrivatePage(array $pages): ?object
    {
        $privatePage = null;
        foreach ($pages as $page) {
            if ($this->wpService->getPostStatus($page->post_id) === 'publish') {
                return $page;
            }
            $privatePage = $page;
        }
        return $privatePage;
    }

    /**
     * Get the page containing the form module for a given post.
     */
    private function getPostWithFormModule(int $postId)
    {
        $moduleId = $this->getModuleIdForPost($postId);
        if (!$moduleId) {
            return null;
        }
        $pages = $this->getPagesWithModule($moduleId);
        if (empty($pages)) {
            return null;
        }
        return $this->findPublishedOrPrivatePage($pages);
    }
}