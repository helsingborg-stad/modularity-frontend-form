<?php

namespace ModularityFrontendForm\Admin;

use WpService\WpService;
use ModularityFrontendForm\Config\Config;

class DisableGutenbergOnSubmissions {

  private bool $isEnabled = true;

  public function __construct(private Config $config, private WpService $wpService){}

  /**
   * Adds necessary WordPress hooks.
   */
  public function addHooks(): void
  {
    $this->wpService->addFilter(
      'use_block_editor_for_post_type', 
      [$this, 'disableGutenberg']
    , 150, 2);
  }

  /**
   * Disables Gutenberg editor for post types that manage submissions.
   *
   * @param bool   $useBlockEditor Whether the block editor is enabled.
   * @param string $postType       The post type being checked.
   * @return bool
   */
  public function disableGutenberg($useBlockEditor, $postType): bool
  {
    if(!$this->isEnabled) {
      return $useBlockEditor;
    }

    // Check if we are on a single admin page
    $isSingleAdminPage = $this->isSingleAdminPage();
    if(!$isSingleAdminPage) {
      return $useBlockEditor;
    }

    // Check if the post type manages submissions,
    $currentPostId = $this->wpService->getTheID();
    if ($this->isSubmittedByForm($currentPostId)) {
      return false;
    }

    return $useBlockEditor;
  }

  /**
   * Checks if the current post is a submission post.
   *
   * @param int $postId The ID of the post to check.
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
   * Checks if the current page is a single admin page using WordPress functions.
   *
   * @return bool
   */
  private function isSingleAdminPage(): bool
  {
    if (!$this->wpService->isAdmin()) {
      return false;
    }
    if (!method_exists($this->wpService, 'getCurrentScreen')) {
      return false;
    }
    
    $screen = $this->wpService->getCurrentScreen();
    if (!$screen) {
      return false;
    }
    return in_array($screen->base, ['post', 'post-new'], true);
  }
}