<?php

namespace ModularityFrontendForm\Api\Submit;

use ModularityFrontendForm\Api\RestApiEndpoint;
use \ModularityFrontendForm\Config\ConfigInterface;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

class Post extends RestApiEndpoint
{
    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'submit/post';
    public const KEY       = 'submitForm';

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
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args'                => [
                'module-id' => [
                    'description' => __('The module id that the request originates from', 'modularity-frontend-form'),
                    'type'        => 'integer',
                    'format'      => 'uri',
                    'required'    => false
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
        $params          = $request->get_json_params();
        $a               = $params['url'] ?? null;

        // Check if the request is valid
        if(!$this->validateNonce($params['nonce'] ?? '')) {
            return rest_ensure_response(new WP_Error(
                'invalid_nonce',
                __('Invalid nonce', 'modularity-frontend-form'),
                array('status' => WP_Http::UNAUTHORIZED)
            ));
        }

        $insert = $this->insertPost();

        if (is_wp_error($insert)) {
            return rest_ensure_response(new WP_Error(
                $insert->get_error_code(),
                $insert->get_error_message(),
                array('status' => WP_Http::BAD_REQUEST)
            ));
        } elseif (is_numeric($insert)) {
            return rest_ensure_response([
                'status' => 'success',
                'message' => __('Post created successfully', 'modularity-frontend-form'),
                'postId' => $insert,
            ]);
        }

        return rest_ensure_response(new WP_Error(
            502,
            __('Unexpected result from post creation', 'modularity-frontend-form'),
            array('status' => WP_Http::BAD_REQUEST)
        ));
    }

    /**
     * Callback function for checking if the current user has permission to submit the form
     *
     * @return bool Whether the user has permission to submit the form
     */
    public function permissionCallback(): bool
    {
        return true; //May be changed to check for specific capabilities
    }

    /**
     * Handles the request to insert a post
     *
     * @param int|null $moduleID The module ID
     * @param array|null $fieldMeta The field meta data
     *
     * @return WP_Error|int The result of the post insertion
     */
    public function insertPost($moduleID = null, $fieldMeta = null): WP_Error|int {
        
        $result = $this->wpService->wpInsertPost([
            'post_title'    => 'Test post',
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'meta_input'   => [
                'module_id' => $moduleID,
                'field_meta' => $fieldMeta,
            ],
        ]);

        if ($this->wpService->isWpError($result)) {
            return $result;
        }

        return $result;
    }

    /**
     * Validates the nonce for the request
     *
     * @param string $nonce The nonce to validate
     *
     * @return bool Whether the nonce is valid
     */
    public function validateNonce($nonce): bool
    {
        return $this->wpService->wpVerifyNonce($nonce, $this->config->getNonceKey());
    }
}