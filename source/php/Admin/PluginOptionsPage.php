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
class PluginOptionsPage implements \Municipio\HooksRegistrar\Hookable
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
            $this->acfService->addOptionsSubPage([
                'page_title'  => __('Frontend Form Options', 'modularity-frontend-form'),
                'menu_title'  => __('Frontend Form', 'modularity-frontend-form'),
                'menu_slug'   => 'mod-frontend-form-options',
                'capability'  => 'manage_options',
                'parent_slug' => 'options-general.php',
            ]);
        });
    }
}