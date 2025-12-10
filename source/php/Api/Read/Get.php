<?php

namespace ModularityFrontendForm\Api\Read;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
use \ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\DataProcessor;
use ModularityFrontendForm\Api\RestApiParams;
use ModularityFrontendForm\Api\RestApiParamEnums;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

use ModularityFrontendForm\Api\RestApiResponseStatusEnums;

use ModularityFrontendForm\Api\Read\GetReturnTypeEnum;

class Get extends RestApiEndpoint
{
    use GetModuleConfigInstanceTrait;

    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'read/get';
    public const KEY       = 'getForm';

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
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => '__return_true',
            'args' => (new RestApiParams($this->wpService, $this->config, $this->moduleConfigFactory))->getParamSpecification(
                RestApiParamEnums::ModuleId,
                RestApiParamEnums::Nonce,
                RestApiParamEnums::PostId,
                RestApiParamEnums::Token
            )
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
        $this->filterReturnTypeSetting();

        $params = (new RestApiParams(
            $this->wpService, 
            $this->config, 
            $this->moduleConfigFactory)
        )->getValuesFromRequest($request);

        //Get fields from post id 
        $fieldData       = $this->acfService->getFields($params->postId, true, false);
        $fieldData       = $this->translateFieldNamesToFieldKeys($params->postId, $fieldData);
        $fieldData       = $this->filterUnmappedFieldKeysForPostType($params->moduleId, $fieldData);

        //Add post title
        //TODO: Control that the post_title option is activated in the module
        //TODO: Prepend post_content if option is selected in the module
        $fieldData       = $this->prependPostTitleToFieldData($fieldData, $params->postId);

        if ($fieldData !== false) {
            return new WP_REST_Response(
                [
                    'status' => RestApiResponseStatusEnums::Success,
                    'data'   => $fieldData,
                ],
                WP_Http::OK
            );
        }

        return new WP_REST_Response(
            [
                'status' => RestApiResponseStatusEnums::Success
            ],
            WP_Http::NOT_FOUND
        );
    }

    /**
     * Adds a filter to ACF taxonomy fields to always return IDs
     */
    private function filterReturnTypeSetting(): void
    {
        $this->wpService->addFilter('acf/load_field', function ($field) {
            switch ($field['type']) {
                case 'taxonomy':
                    $field['return_format'] = GetReturnTypeEnum::ID->value;
                    break;
                default:
                    return $field;
            }
            return $field;
        });
    }

    /**
     * Prepend the post title to the field data
     *
     * @param array $fieldData The field data
     * @param int $postId The post ID
     *
     * @return array The field data with the post title prepended
     */
    private function prependPostTitleToFieldData(array $fieldData, int $postId): array
    {
        return ['post_title' => $this->wpService->getPost($postId)->post_title ?? null] + $fieldData;
    }

    /**
     * Translates field names to field keys
     *
     * @param int $postId The post ID
     * @param array $fields The fields to translate
     *
     * @return array The translated fields
     */
    private function translateFieldNamesToFieldKeys(int $postId, array $fields): array
    {
        $translatedFields = [];

        foreach($fields as $key => $fieldValue) {
            $translatedFields[$this->translateFieldNameToFieldKey($postId, $key)] = $fieldValue;
        }
        return $translatedFields;
    }

    /**
     * Translates a field name to a field key
     *
     * @param int $postId The post ID
     * @param string $fieldName The field name to translate
     *
     * @return string The translated field key
     */
    private function translateFieldNameToFieldKey(int $postId, string $fieldName): string
    {
        return $this->wpService->getPostMeta($postId, "_" . $fieldName, true) ?? $fieldName;
    }

    /**
     * Removes fields that are not registered in any of the field groups mapped to the post type.
     * 
     * @param array $fieldKeys The fields to check
     * @param string $postType The post type to check against
     * @param array $defaultKeys The default keys to include, if any.
     */
    private function filterUnmappedFieldKeysForPostType($moduleId, $fieldData): array
    {
        $fieldKeysRegisteredAsFormFields = $this->getModuleConfigInstance($moduleId)->getFieldKeysRegisteredAsFormFields();

        $fieldData = array_intersect_key(
            $fieldData,
            array_flip($fieldKeysRegisteredAsFormFields)
        );

        return $fieldData;
    }
}