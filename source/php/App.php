<?php

namespace ModularityFrontendForm;

use WpService\WpService;

use Api\RestApiEndpointsRegistry;
use Api\Submit\Post;

use \Modularity\HooksRegistrar\Hookable;

/**
 * Class App
 * @package ModularityFrontendForm
 */
class App implements \Modularity\HooksRegistrar\Hookable {

    public function __construct(private WpService $wpService){}

    /**
     * Add hooks to WordPress
     * @return void
     */
    public function addHooks(): void
    {
        $this->wpService->addAction('plugins_loaded', array($this, 'registerModule'));

        //Register the API routes
        foreach (['rest_api_init','init'] as $action) {
            $this->wpService->addAction($action, array($this, 'registerApi'));
        }

        // Register the API routes as a js object
        (new Api\RestApiRoutes(
            $this->wpService,
            Api\RestApiEndpointsRegistry::class
        ))->addHooks();
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
        } else {
            throw new \Exception('Modularity is not active: frontend form module cannot be registered.');
        }
    }

    /**
     * Register the API
     * @return void
     */
    public function registerApi()
    {
        $restEndpoints = [
            new Api\Submit\Post()
        ];

        $this->wpService->applyFilters(
            'modularity_frontend_form_rest_api_endpoints',
            $restEndpoints
        );

        foreach ($restEndpoints as $endpoint) {
            Api\RestApiEndpointsRegistry::add($endpoint);
        }

        if ($this->wpService->currentAction() === 'rest_api_init') {
            Api\RestApiEndpointsRegistry::register();
        }
    }
}
