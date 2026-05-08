<?php

namespace ModularityFrontendForm\Admin;

use ModularityFrontendForm\Config\ConfigInterface;
use WpService\WpService;

/**
 * Class EditSubmissionOnFrontendInterface
 * 
 * This class manages the ability to edit frontend submissions, 
 * by clicking a link in the admin interface.
 * 
 * @package ModularityFrontendForm\Admin
 */
class DisplayEditLinkInterfaceNotice implements \Municipio\HooksRegistrar\Hookable
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
            $postId = $this->wpService->getTheId();
            $this->maybeShowFrontendNotice(
                $postId,
                $this->constructFrontendUrl($postId)
            );
        });
    }

    private function getHoldingPostId(int $postId): ?int
    {
        $holdingPostId = $this->wpService->getPostMeta(
            $postId,
            $this->config->getMetaDataNamespace('holding_post_id'),
            true
        );

        return $holdingPostId ? (int) $holdingPostId : null;
    }

    /**
     * Constructs the frontend URL with token and postId query parameters.
     *
     * @param int $postId
     * @return string
     */
    private function constructFrontendUrl(int $postId): string
    {
        // Get the post password token and permalink
        $token = $this->getPostPasswordToken($postId);
        $url   = $this->wpService->getPermalink(
            $this->getHoldingPostId($postId) ?? $postId
        );

        // Append query parameters
        $url   = add_query_arg('token', $token, $url);
        $url    = add_query_arg('postId', $postId, $url);

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
                '<div class="notice notice-info" style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px 10px 10px; gap: 20px;">
                    <div>
                        <h3 style="margin:0 0 5px 0;">%s</h3>%s
                    </div>
                    <div>
                        <a href="%s" target="_blank" class="button button-secondary">%s</a>
                    </div>
                </div>',
                esc_html__('Frontend Submission', 'modularity-frontend-form'),
                esc_html__('This content was created by a end user, you may edit as a end user by clicking the button.', 'modularity-frontend-form'),
                esc_url($frontendUrl),
                esc_html__('View on frontend', 'modularity-frontend-form')
            );
        }
    }
}