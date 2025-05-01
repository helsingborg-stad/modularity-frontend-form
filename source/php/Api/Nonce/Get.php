<?php

namespace ModularityFrontendForm\Api\Nonce;

use ModularityFrontendForm\Api\RestApiEndpoint;
use ModularityFrontendForm\Config\ConfigInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

class Get extends RestApiEndpoint
{
    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'nonce/get';
    public const KEY       = 'nonceGet';

    public function __construct(
        private WpService $wpService,
        private ConfigInterface $config
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
          'permission_callback' => array($this, 'permissionCallback')
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
        return new WP_REST_Response([
            'nonce' => $this->wpService->wpCreateNonce($this->config->getNonceKey()),
        ]);
    }

    /**
     * Callback function for checking if the current user has permission to get a nonce
     *
     * @return bool Whether the user has permission to get a nonce
     */
    public function permissionCallback(): bool
    {
        return true;
    }
}