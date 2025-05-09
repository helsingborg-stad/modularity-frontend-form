<?php

namespace ModularityFrontendForm\Api\Nonce;

use ModularityFrontendForm\Api\RestApiEndpoint;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

class Get extends RestApiEndpoint
{
    use GetModuleConfigInstanceTrait;

    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'nonce/get';
    public const KEY       = 'nonceGet';

    public function __construct(
        private WpService $wpService,
        private ConfigInterface $config,
        private ModuleConfigFactoryInterface $moduleConfigFactory
    ) {}

    /**
     * Registers a REST route
     *
     * @return bool Whether the route was registered successfully
     */
    public function handleRegisterRestRoute(): bool
    {
      return register_rest_route(self::NAMESPACE, self::ROUTE, array(
          'methods'             => WP_REST_Server::READABLE,
          'callback'            => array($this, 'handleRequest'),
          'permission_callback' => '__return_true',
          'args'                => [
                'module-id' => [
                    'description' => __('The module id that the request originates from', 'modularity-frontend-form'),
                    'type'        => 'integer',
                    'format'      => 'uri',
                    'required'    => true
                ]
            ]
      ));
    }

    /**
     * Handles a REST request and sideloads an image
     *
     * @param WP_REST_Request $request The REST request object
     *
     * @return WP_REST_Response|WP_Error The sideloaded image URL or an error object if the sideload fails
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $moduleId = $request->get_params()['module-id'] ?? null;

        $nonceKey = $this->getModuleConfigInstance(
            $moduleId
        )->getModuleIsSubmittable();

        return new WP_REST_Response([
            'nonce' => $this->wpService->wpCreateNonce($nonceKey),
        ]);
    }
}