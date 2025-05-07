<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
use \ModularityFrontendForm\Config\ConfigInterface;
use WP;
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
        private AcfService $acfService,
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
            'permission_callback' => '__return_true',
            'args'                => [
                'module-id' => [
                    'description' => __('The module id that the request originates from', 'modularity-frontend-form'),
                    'type'        => 'integer',
                    'format'      => 'uri',
                    'required'    => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param) && $this->isFormModuleID($param);
                    },
                ]
            ]
        ));
    }

    /**
     * Checks if the module ID is a form module
     *
     * @param int $moduleID The module ID to check
     *
     * @return bool Whether the module ID is a form module
     */
    private function isFormModuleID(int $moduleID): bool
    {
        // Form submissions can only originate from the form module
        if ($this->wpService->getPostType($moduleID) !== $this->config->getModuleSlug()) {
            return false;
        }

        // Form submissions can only be submitted from published and private modules
        if (!in_array($this->wpService->getPostStatus($moduleID), ['publish', 'private'])) {
            return false;
        }

        return true;
    }


    /**
     * Handles a REST request to submit a form
     *
     * @param WP_REST_Request $request The REST request object
     *
     * @return WP_REST_Response|WP_Error The response object or an error
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response|WP_Error
    {
        $params          = $request->get_params();

        $moduleID        = $params['module-id'] ?? null;
        $fieldMeta       = $params['mod-frontedform'] ?? null;

        // Check if the request is valid
        if (!$this->validateNonce($params['nonce'] ?? '')) {
            return rest_ensure_response(
                new WP_Error(
                    'invalid_nonce',
                    __('Invalid nonce', 'modularity-frontend-form'),
                    array('status' => WP_Http::UNAUTHORIZED)
                )
            );
        }

        return $this->formatInsertResponse(
            $this->insertPost($moduleID, $fieldMeta)
        );
    }

    /**
     * Formats the response for the insert post request
     *
     * @param WP_Error|int $result The result of the post insertion
     *
     * @return WP_REST_Response The formatted response
     */
    private function formatInsertResponse(WP_Error|int $result): WP_REST_Response|WP_Error {
        if (is_wp_error($result) || $result === 0) {
            return rest_ensure_response(new WP_Error(
                $result instanceof WP_Error ? $result->get_error_code() : 'post_not_created',
                $result instanceof WP_Error ? $result->get_error_message() : __('Post not created', 'modularity-frontend-form'),
                [
                    'status' => WP_Http::BAD_REQUEST,
                    'details' => $result instanceof WP_Error ? $result->get_error_data() : null
                ]
            ));
        }
    
        return rest_ensure_response([
            'status' => 'success',
            'message' => __('Post created successfully', 'modularity-frontend-form'),
            'postId' => $result,
        ]);
    }

    /**
     * Handles the request to insert a post
     *
     * @param int|null $moduleID The module ID
     * @param array|null $fieldMeta The field meta data
     *
     * @return WP_Error|int The result of the post insertion
     */
    public function insertPost(int $moduleID, array|null $fieldMeta): WP_Error|int {

        // Check if all fields exists on the target post type
        if($invalidFields = $this->requestIncludesFiledNotPresentOnTargetPostType($fieldMeta, 'post')) {
            return new WP_Error(
                'invalid_field',
                __('Invalid field keys sent.', 'modularity-frontend-form'),
                ['invalid_fields' => array_values($invalidFields)],
            );
        }

        
        $result = $this->wpService->wpInsertPost([
            'post_title'    => 'Test post',
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'meta_input'   => [
                'module_id' => $moduleID,
                'field_meta' => $fieldMeta,
            ],
        ]);

        // Post Successfully created, store the fields
        if (!$this->wpService->isWpError($result) && !is_null($fieldMeta)) {
            $sanitizedFieldMetaKeys = $this->filterUnmappedFieldKeysForPostType(
                array_keys($fieldMeta),
                'post'
            );
            $this->storeFields($fieldMeta, $result);
        }

        return $result;
    }

    /**
     * Stores the fields in the database
     *
     * @param array $fields The fields to store
     * @param int $postId The ID of the post to store the fields for
     */
    public function storeFields($fields, $postId)
    {
        $sanitizedFieldMetaKeys = $this->filterUnmappedFieldKeysForPostType(
            array_keys($fields),
            'post'
        );

        foreach ($sanitizedFieldMetaKeys as $key) {
            if (isset($fields[$key])) {
                $this->acfService->updateField(
                    $key, 
                    $this->santitileFieldValue($fields[$key], $key), 
                    $postId
                );
            }
        }
    }

    /**
     * Checks if the request includes fields that are not present on the target post type
     *
     * @param array $fieldMeta The field meta data
     * @param string $postType The post type to check against
     *
     * @return array The invalid field keys
     */
    public function requestIncludesFiledNotPresentOnTargetPostType($fieldMeta, $postType)
    {
        $fieldKeys = array_keys($fieldMeta);

        $validKeys = $this->filterUnmappedFieldKeysForPostType(
            $fieldKeys,
            $postType
        );

        return array_diff($fieldKeys, $validKeys);
    }

    /**
     * Removes fields that are not registered in any of the field groups mapped to the post type
     *
     * @param array $fields The fields to check
     * @param string $postType The post type to check against
     * @param array $defaultKeys The default keys to include, if any.
     * 
     * @return array The filtered fields
     */
    private function filterUnmappedFieldKeysForPostType(array $fieldKeys, string $postType, array $defaultKeys = []): array
    {
        $validKeys = $defaultKeys;

        $fieldGroups = $this->acfService->getFieldGroups(['post_type' => $postType]);

        foreach ($fieldGroups as $group) {
            if(!isset($group['key'])) {
                continue;
            }

            $fields = $this->acfService->acfGetFields($group['key']);

            if(!is_array($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                if (isset($field['key']) && in_array($field['key'], $fieldKeys, true)) {
                    $validKeys[] = $field['key'];
                }
            }
        }

        return array_unique($validKeys);
    }

    /**
     * Sanitizes the field value based on its type
     *
     * @param mixed $value The value to sanitize
     * @param string $fieldKey The key of the field
     *
     * @return mixed The sanitized value
     */
    private function santitileFieldValue($value, $fieldKey) {
        $field = $this->acfService->getField($fieldKey);

        if (isset($field['type'])) {
            switch ($field['type']) {
                case 'text':
                    return sanitize_text_field($value);
                case 'email':
                    return sanitize_email($value);
                case 'url':
                    return esc_url_raw($value);
                case 'number':
                    return intval($value);
                default:
                    return $value;
            }
        }

        return $value;
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
        return (bool) $this->wpService->wpVerifyNonce(
            $nonce, 
            $this->config->getNonceKey()
        );
    }
}