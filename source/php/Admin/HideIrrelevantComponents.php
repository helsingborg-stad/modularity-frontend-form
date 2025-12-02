<?php

namespace ModularityFrontendForm\Admin;

use ModularityFrontendForm\Config\ConfigInterface;
use WpService\WpService;

/**
 * Class HideIrrelevantComponents
 * 
 * This class will hide irrelevant components in the admin interface
 * for posts created via frontend submission.
 * 
 * @package ModularityFrontendForm\Admin
 */
class HideIrrelevantComponents implements \Municipio\HooksRegistrar\Hookable
{
    private bool $isEnabled = true;

    //Define meta boxes and post features that should 
    //never be available for frontend submitted posts
    private const NEVER_SUPPORTED_META_BOXES = [
        'pageparentdiv',
        'customer-feedback-summary-meta',
        'slugdiv',
        'tsf-inpost-box',
        'acf-group_636e424039120', // Screen reader language
        'acf-group_56c33cf1470dc', // Display settings
        'acf-group_56d83cff12bb3', // Navigation settings
        'acf-group_646c5d26e3359', // Google translate settings
        'acf-group_6784bb5c51d70', // Post Icon settings
        'acf-group_64227d79a7f57', // Quicklinks settings
    ];

    //Define post features that may be conditionally removed
    private const POST_FEATURES = [
        'editor',
        'title',
    ];

    public function __construct(private ConfigInterface $config, private WpService $wpService)
    {
    }

    /**
     * Add hooks to WordPress
     * @return void
     */
    public function addHooks(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Get current post type
        $currentPostType = $this->getCurrentPostType();

        // Remove never supported post features
        $this->wpService->addAction('add_meta_boxes', function () use ($currentPostType) {
            foreach (self::NEVER_SUPPORTED_META_BOXES as $metaBox) {
                $this->wpService->removeMetaBox($metaBox, $currentPostType, 'normal');
                $this->wpService->removeMetaBox($metaBox, $currentPostType, 'side');
                $this->wpService->removeMetaBox($metaBox, $currentPostType, 'advanced');
            }
        }, 600);

        // Remove post features conditionally
        foreach (self::POST_FEATURES as $postFeature) {

            if($postFeature === 'editor' && $this->formHasContentFieldSupport()) {
                continue;
            }

            if($postFeature === 'title' && $this->formHasTitleFieldSupport()) {
                continue;
            }

            $this->wpService->addAction('init', function () use ($currentPostType, $postFeature) {
                $this->wpService->removePostTypeSupport($currentPostType, $postFeature);
            }, 600);
        }
    }

    /**
     * Checks if the form has content field support.
     * TODO: Implement dynamic check based on form configuration.
     * 
     * @return bool
     */
    private function formHasContentFieldSupport(): bool
    {
        return false;
    }

    /**
     * Checks if the form has title field support.
     * TODO: Implement dynamic check based on form configuration.
     * 
     * @return bool
     */
    private function formHasTitleFieldSupport(): bool
    {
        return true;
    }

    /**
     * Checks if the functionality is enabled and if the current post
     * was submitted via the frontend form.
     * 
     * @return bool
     */
    private function isEnabled() : bool
    {
        $postId = $this->getPostId();
        if (empty($postId)) {
            return false;
        }
        return $this->isEnabled && $this->isExternallySubmitted(
            $postId
        );
    }

    /**
     * Checks if the post was submitted via the frontend form.
     * @param int $postId
     * @return bool
     */
    private function isExternallySubmitted(int $postId) : bool
    {
        $hasModuleId = $this->wpService->getPostMeta(
            $postId,
            $this->config->getMetaDataNamespace('module_id'),
            true
        );
        return !empty($hasModuleId);
    }

    /**
     * Get the current post ID.
     * 
     * @return null|int
     */
    private function getPostId() : ?int
    {
        $postId = $this->wpService->getTheId();
        if(empty($postId)) {
            $postId = $_GET['post'] ?? intval($_POST['post_ID']) ?? null;
        }
        if(empty($postId)) {
            global $post;
            if ($post instanceof WP_Post) {
                $postId = $post->ID;
            }
        }
        if(empty($postId)) {
            return null;
        }
        return $postId;
    }

    /**
     * Get the current post type.
     * 
     * @return null|string
     */
    private function getCurrentPostType(): ?string
    {
        $postId = $this->getPostId();
        if(empty($postId)) {
            return null;
        }
        $post = $this->wpService->getPost($postId);
        if($post === null) {
            return null;
        }
        return $post->post_type;
    }
}