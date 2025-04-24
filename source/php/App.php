<?php

namespace ModularityFrontendForm;

use WpService\WpService;

use Api\RestApiEndpointsRegistry;
use Api\Submit\Post;

/**
 * Class App
 * @package ModularityFrontendForm
 */
class App 
{
    public function __construct(private WpService $wpService)
    {
        add_action('plugins_loaded', array($this, 'registerModule'));
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYFRONTENDFORM_PATH . 'source/php/Module/',
                'FrontendForm'
            );
        }
    }

    public function registerApi()
    {
        $restEndpoints = [
            'sideload' => new Api\Submit\Post()
        ];

        $this->wpService->applyFilters(
            'modularity_frontend_form_rest_api_endpoints',
            $restEndpoints
        );

        foreach ($restEndpoints as $endpoint) {
            Api\RestApiEndpointsRegistry::add($endpoint);
        }
    }
}
