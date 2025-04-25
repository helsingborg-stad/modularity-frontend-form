<?php

namespace ModularityFrontendForm\Api\Submit;

use ModularityFrontendForm\Api\RestApiEndpoint;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Post extends RestApiEndpoint
{
    protected const NAMESPACE = 'modularity-frontend-form/v1';
    protected const ROUTE     = 'submit/post';
    protected const KEY       = 'submit-post';

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
                  'description' => __('The module id that the request originates from', 'municipio'),
                  'type'        => 'integer',
                  'format'      => 'uri',
                  'required'    => true
              ],
              'data' => [
                  'description' => __('Description.', 'municipio'),
                  'type'        => 'string',
                  'required'    => false,
                  'default'     => null
              ],
              'return'      => [
                  'description' => __('Return', 'municipio'),
                  'type'        => 'string',
                  'enum'        => ['html', 'src', 'id'],
                  'required'    => false,
                  'default'     => 'html'
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
        $a              = $params['url'] ?? null;

        /*if (is_wp_error($a)) {
            $error = new WP_Error(
                $a->get_error_code(),
                $a->get_error_message(),
                array('status' => WP_Http::BAD_REQUEST)
            );
            return rest_ensure_response($error);
        }*/ 

        return rest_ensure_response($a);
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
}