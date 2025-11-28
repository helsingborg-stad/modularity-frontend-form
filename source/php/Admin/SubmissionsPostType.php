<?php

namespace ModularityFrontendForm\Admin;

use ModularityFrontendForm\Config\ConfigInterface;
use WpService\WpService;
use AcfService\AcfService;

/**
 * Class EditSubmissionOnFrontendInterface
 * 
 * This class manages the ability to edit frontend submissions, 
 * by clicking a link in the admin interface.
 * 
 * @package ModularityFrontendForm\Admin
 */
class SubmissionsPostType implements \Municipio\HooksRegistrar\Hookable
{
    private bool $isEnabled = true;

    public function __construct(
      private ConfigInterface $config, 
      private WpService $wpService,
      private AcfService $acfService)
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

        $this->wpService->addAction('init', function () {
            $this->wpService->registerPostType('mod-frontend-form-submission', [
                'label' => __('Frontend Form Submissions', 'modularity-frontend-form'),
                'public' => false,
                'show_ui' => true,
                'capability_type' => 'post',
                'supports' => ['title', 'editor', 'custom-fields'],
                'menu_icon' => 'dashicons-feedback',
            ]);
        });
    }
}