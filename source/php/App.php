<?php

namespace ModularityFrontendForm;

use AcfService\AcfService;
use WpService\WpService;

use \Modularity\HooksRegistrar\Hookable;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;

/**
 * Class App
 * @package ModularityFrontendForm
 */
class App implements Hookable {

    public function __construct(
        private WpService $wpService, 
        private AcfService $acfService, 
        private ConfigInterface $config, 
        private ModuleConfigFactoryInterface $moduleConfigFactory
    ){
    }

    /**
     * Add hooks to WordPress
     * @return void
     */
    public function addHooks(): void
    {
        $this->wpService->addAction('plugins_loaded', array($this, 'registerModule'));
        $this->wpService->addAction('init', array($this, 'registerFieldsPostType'));

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
     * Register the post type
     * @return void
     */
    public function registerFieldsPostType()
    {
        $this->wpService->registerPostType('frontend_form_fields', [
            'labels' => [
                'name'          => __('Frontend form fields', 'modularity-frontend-form'),
                'singular_name' => __('Frontend form fields', 'modularity-frontend-form'),
                'menu_name'     => __('Form fields', 'modularity-frontend-form'),
            ],
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => false,
            'menu_icon'     => 'dashicons-feedback',
            'supports'      => ['title'],
        ]);
    }

    /**
     * Register the API
     * @return voidxw
     */
    public function registerApi()
    {
        $restEndpoints = [
            new Api\Submit\Post($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory),
            new Api\Submit\Update($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory),
            new Api\Read\Get($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory),
            new Api\Nonce\Get($this->wpService, $this->config, $this->moduleConfigFactory),
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
