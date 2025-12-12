<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
use ModularityFrontendForm\Api\RestApiParams;
use ModularityFrontendForm\Api\RestApiParamEnums;
use \ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\DataProcessor;
use WP;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Handlers\NullHandler;

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
            'args' => (
                new RestApiParams($this->wpService, $this->config, $this->moduleConfigFactory)
            )->getParamSpecification(
                RestApiParamEnums::HoldingPostId,
                RestApiParamEnums::ModuleId,
                RestApiParamEnums::Nonce,
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
        $params = (new RestApiParams(
            $this->wpService, 
            $this->config, 
            $this->moduleConfigFactory)
        )->getValuesFromRequest($request);

        $data = $request->get_params();

        $sideloaded = $this->handleSideloadedFiles($request);
        
        var_dump($sideloaded); // For debugging purposes
            die;

        // Handler factories
        $validatorFactory   = new ValidatorFactory($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory);
        $handlerFactory     = new HandlerFactory($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory);

        // Creates the data processor
        $dataProcessor = new DataProcessor(
            $validatorFactory->createInsertValidators($params->moduleId),
            $handlerFactory->createHandlers($params),
            $handlerFactory->createNullHandler($params),
        );

        $dataProcessorResult = $dataProcessor->process($data);

        if($dataProcessorResult !== true) {
            return $this->wpService->restEnsureResponse(
                $dataProcessor->getFirstError() ?? new WP_Error(
                    RestApiResponseStatusEnums::GenericError,
                    __('An error occurred while processing the form', 'modularity-frontend-form'),
                    [
                        'status' => WP_Http::BAD_REQUEST
                    ]
                )
            );
        }

        return $this->wpService->restEnsureResponse([
            'status' => RestApiResponseStatusEnums::Success,
            'message' => __('Form submitted successfully', 'modularity-frontend-form')
        ]);
    }

    /**
     * Handles sideloaded files from a nested $_FILES structure as described.
     *
     * @param WP_REST_Request $request
     * @return array|WP_Error
     */
    private function handleSideloadedFiles(WP_REST_Request $request)
    {
        $files = $request->get_file_params()['mod-frontend-form'] ?? null;

        if (!$files || !is_array($files)) {
            return new WP_Error(
                'no_file_uploaded',
                __('No file was uploaded.'),
                ['status' => 400]
            );
        }

        // Flatten the nested structure: [name][field_key][0]
        $fieldKeys = array_keys($files['name'] ?? []);
        $results = [];

        foreach ($fieldKeys as $fieldKey) {
            // Support multiple files per field (array of files)
            $fileCount = is_array($files['name'][$fieldKey]) ? count($files['name'][$fieldKey]) : 0;

            for ($i = 0; $i < $fileCount; $i++) {
                $fileArray = [
                    'name'     => $files['name'][$fieldKey][$i] ?? '',
                    'type'     => $files['type'][$fieldKey][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$fieldKey][$i] ?? '',
                    'error'    => $files['error'][$fieldKey][$i] ?? 4,
                    'size'     => $files['size'][$fieldKey][$i] ?? 0,
                ];

                if (empty($fileArray['name']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                // Load WordPress media handling utilities
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attachment_id = media_handle_sideload($fileArray, 0);

                if (is_wp_error($attachment_id)) {
                    $results[$fieldKey][] = $attachment_id;
                } else {
                    $results[$fieldKey][] = [
                        'id'   => $attachment_id,
                        'url'  => wp_get_attachment_url($attachment_id),
                        'type' => get_post_mime_type($attachment_id),
                    ];
                }
            }
        }

        return $results;
    }
}