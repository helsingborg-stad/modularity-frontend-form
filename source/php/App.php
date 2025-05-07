<?php

namespace ModularityFrontendForm;

use AcfService\AcfService;
use WpService\WpService;

use ModularityFrontendForm\Api\RestApiEndpointsRegistry;
use ModularityFrontendForm\Api\Submit\Post;

use ModularityFrontendForm\Config\Config;

use \Modularity\HooksRegistrar\Hookable;

/**
 * Class App
 * @package ModularityFrontendForm
 */
class App implements \Modularity\HooksRegistrar\Hookable {

    public function __construct(private WpService $wpService, private AcfService $acfService, private Config $config){}

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
     * @return voidxw
     */
    public function registerApi()
    {
        $restEndpoints = [
            new Api\Submit\Post($this->wpService, $this->acfService, $this->config),
            new Api\Nonce\Get($this->wpService, $this->config),
        ];

        $this->wpService->applyFilters(
            $this->config->createFilterKey('Api/Endpoints'),
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
