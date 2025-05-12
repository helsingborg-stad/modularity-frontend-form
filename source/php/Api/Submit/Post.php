<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
use \ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use WP;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

class Post extends RestApiEndpoint
{
    use GetModuleConfigInstanceTrait;

    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'submit/post';
    public const KEY       = 'submitForm';

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
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
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => '__return_true',
            'args'                => [
                'module-id' => [
                    'description' => __('The module id that the request originates from', 'modularity-frontend-form'),
                    'type'        => 'integer',
                    'format'      => 'uri',
                    'required'    => true,
                    'validate_callback' => function ($moduleId) {
                        return $this->getModuleConfigInstance(
                            $moduleId
                        )->getModuleIsSubmittable();
                    },
                ]
            ]
        ));
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
        $moduleId        = $request->get_params()['module-id']                          ?? null;
        $fieldMeta       = $request->get_params()[$this->config->getFieldNamespace()]   ?? null;
        $nonce           = $request->get_params()['nonce']                              ?? '';

        // Check if the request is valid
        if (!$this->validateNonce($nonce, $moduleId)) {
            return rest_ensure_response(
                new WP_Error(
                    'invalid_nonce',
                    __('Invalid nonce', 'modularity-frontend-form'),
                    array('status' => WP_Http::UNAUTHORIZED)
                )
            );
        }

        return $this->formatInsertResponse(
            $this->insertPost($moduleId, $fieldMeta)
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

        // Get the post type to submit to
        $targetPostType = $this->getModuleConfigInstance(
            $moduleID
        )->getTargetPostType();

        // Check if all fields exists on the target post type
        if($invalidFields = $this->requestIncludesFiledNotPresentOnTargetPostType($fieldMeta, $targetPostType)) {
            return new WP_Error(
                'invalid_field',
                __('Some fields do not belong to this form.', 'modularity-frontend-form'),
                ['invalid_fields' => array_values($invalidFields)],
            );
        }

        if($invalidFields = $this->validateRequestInputValuesToFieldSpecifications($fieldMeta)) {
            return new WP_Error(
                'invalid_field_values',
                __('Some fields contained invalid data.', 'modularity-frontend-form'),
                ['invalid_fields' => array_values($invalidFields)],
            );
        }
        
        $result = $this->wpService->wpInsertPost([
            'post_title'    => 'Test post',
            'post_type'     => $targetPostType,
            'post_status'   => 'publish',
            'meta_input'   => [
                'module_id' => $moduleID,
                'field_meta' => $fieldMeta,
            ],
        ]);

        // Post Successfully created, store the fields
        if (!$this->wpService->isWpError($result) && !is_null($fieldMeta)) {
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
    public function storeFields(array $fields, int $postId)
    {
        foreach ($fields as $key => $value) {
            if (isset($fields[$key])) {
                $result = $this->acfService->updateField(
                    $key, 
                    $this->santitileFieldValue($value, $key), 
                    $postId
                );

                if($result === false) {
                    throw new WP_Error(
                        'store_field_failed',
                        __('Could not save form metadata.', 'modularity-frontend-form')
                    );
                }
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
     * Validates the request input values against the field specifications
     *
     * @param array $fieldMeta The field meta data
     *
     * @return array The invalid fields
     */
    public function validateRequestInputValuesToFieldSpecifications($fieldMeta): ?array
    {
        $invalidFields = [];
        foreach ($fieldMeta as $key => $value ) {
            if($field = acf_get_field($key)) { //TODO: Add to wp service
                $isValid = acf_validate_value($value, $field, ""); //TODO: Add to wp service
                if(!$isValid) {
                    $invalidFields[] = [
                        'key' => $key,
                        'label' => $field['label'] ?? $key
                    ];
                }
            }
        }
        return $invalidFields ?: null;
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
    private function santitileFieldValue($value, $fieldKey = '') {
        return $value;
    }

    /**
     * Validates the nonce for the request
     *
     * @param string $nonce The nonce to validate
     * @param int $moduleId The module ID
     *
     * @return bool Whether the nonce is valid
     */
    public function validateNonce(string $nonce, int $moduleId): bool
    {
        $nonceKey = $this->wpService->wpCreateNonce(
            $this->getModuleConfigInstance(
                $moduleId
            )->getNonceKey()
        );
        return $nonceKey === $nonce;
    }
}