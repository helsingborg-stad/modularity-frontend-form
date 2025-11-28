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
class LegalTaxonomies implements \Municipio\HooksRegistrar\Hookable
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

        $this->wpService->addAction('init', fn() => $this->registerTaxonomy(
            __('Data Categories', 'modularity-frontend-form'),
            'frontendform-legal-data-categories'
        ));

        $this->wpService->addAction('init', fn() => $this->registerTaxonomy(
            __('Data Processors', 'modularity-frontend-form'),
            'frontendform-legal-data-processors'
        ));
    }

    /**
     * Registers a taxonomy with the given label and key.
     *
     * @param string $label
     * @param string $key
     * @return void
     */
    private function registerTaxonomy($label, $key) {
        $this->wpService->registerTaxonomy($key, ['mod-frontend-form'], [
            'label' => $label,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true
        ]);
    }
}