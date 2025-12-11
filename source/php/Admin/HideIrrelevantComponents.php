<?php

namespace ModularityFrontendForm\Admin;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactory;
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

    private $moduleConfig;

    //Define meta boxes and post features that should 
    //never be available for frontend submitted posts
    private const NEVER_SUPPORTED_META_BOXES = [
        'slugdiv'
    ];

    //Define post features that may be conditionally removed
    private const POST_FEATURES = [
        'editor',
        'title',
    ];

    public function __construct(
        private ConfigInterface $config, 
        private WpService $wpService, 
        private ModuleConfigFactory $moduleConfigFactory
    )
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

        //Get module config instance
        $this->moduleConfig = $this->moduleConfigFactory->create(
            $this->getFormModuleId(
                $this->getPostId()
            ) ?? 0
        );

        //Get current post type
        $currentPostType = $this->getCurrentPostType();

        $this->wpService->addAction('add_meta_boxes', function () use ($currentPostType) {
           $this->removeNeverSupportedMetaBoxes($currentPostType);
        }, 600);
        $this->wpService->addAction('init', function () use ($currentPostType) {
            $this->dynamicPostFeatures($currentPostType);
        }, 600);
    }

    /**
     * Remove post features that are not enabled
     * for frontend submitted posts.
     * 
     * @param string $currentPostType
     * 
     * @return void
     */
    public function dynamicPostFeatures(string $currentPostType): void
    {
        $dynamicPostFeatures = $this->moduleConfig->getDynamicPostFeatures() ?? [];
        foreach (self::POST_FEATURES as $postFeature) {
            if(in_array($postFeature, $dynamicPostFeatures)) {
                continue;
            }
            $this->wpService->removePostTypeSupport($currentPostType, $postFeature);
        }
    }

    /**
     * Remove meta boxes that are never supported
     * for frontend submitted posts.
     * 
     * @param string $currentPostType
     * 
     * @return void
     */
    public function removeNeverSupportedMetaBoxes(string $currentPostType) : void
    {
        foreach (self::NEVER_SUPPORTED_META_BOXES as $metaBox) {
            $this->wpService->removeMetaBox($metaBox, $currentPostType, 'normal');
            $this->wpService->removeMetaBox($metaBox, $currentPostType, 'side');
            $this->wpService->removeMetaBox($metaBox, $currentPostType, 'advanced');
        }
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
        return $this->getFormModuleId($postId) !== null;
    }
    
    /**
     * Get the form module ID from post meta data.
     * 
     * @param int $postId
     * @return null|int
     */
    private function getFormModuleId(int $postId) : ?int
    {
        $moduleId = $this->wpService->getPostMeta(
            $postId,
            $this->config->getMetaDataNamespace('module_id'),
            true
        );
        if(empty($moduleId)) {
            return null;
        }
        return intval($moduleId);
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
            $postId = $_GET['post'] ?? (isset($_POST['post_ID']) ? intval($_POST['post_ID']) : null);
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
        if(is_numeric($postId)) {
            return intval($postId);
        }
        return null;
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