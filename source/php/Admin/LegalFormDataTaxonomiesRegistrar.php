<?php

namespace ModularityFrontendForm\Admin;

use ModularityFrontendForm\Config\ConfigInterface;
use WpService\WpService;
use AcfService\AcfService;

/**
 * Class LegalFormDataTaxonomiesRegistrar
 * 
 * This class registers the taxonomies used for legal form data processing.
 * 
 * @package ModularityFrontendForm\Admin
 */
class LegalFormDataTaxonomiesRegistrar implements \Municipio\HooksRegistrar\Hookable
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
            'fe-form-data-categories'
        ));

        $this->wpService->addAction('init', fn() => $this->registerTaxonomy(
            __('Data Processors', 'modularity-frontend-form'),
            'fe-form-data-processors'
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
        $registerResult = $this->wpService->registerTaxonomy($key, ['mod-frontend-form'], [
            'label' => $label,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true
        ]);

        // Handle potential registration errors
        if($this->wpService->isWpError($registerResult)) {
            throw new \Exception(
            'Failed to register taxonomy: ' 
            . $registerResult->get_error_message()
            );
        }
    }
}