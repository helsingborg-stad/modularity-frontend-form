<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
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

use ModularityFrontendForm\Api\RestApiResponseStatus;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;

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
        $postId          = $request->get_params()['post-id']                            ?? null;

        //Get validators
        $validators = (function () use ($moduleId) {
            $validatorFactory = new ValidatorFactory($this->wpService, $this->acfService, $this->config);
            return $validatorFactory->createInsertValidators($moduleId) ?? [];
        })();

        // Get handlers
        /*$handlers = (function () use ($moduleId) {
            $handlerFactory = new HandlerFactory($this->wpService, $this->acfService, $this->config);
            return $handlerFactory->createHandlers($moduleId) ?? [];
        })();*/ 
        $handlers = [];

        // Validate & insert
        $result = new DataProcessor(
            $validators,
            $handlers,
            $this->config,
            $this->getModuleConfigInstance($moduleId),
            $moduleId
        );

        if($result !== true) {
            return rest_ensure_response([
                'status' => RestApiResponseStatus::Error,
                'message' => __('Something went wrong when saving the form.', 'modularity-frontend-form'),
                'errors' => $result,
            ]);
        }

        return rest_ensure_response([
            'status' => RestApiResponseStatus::Success,
            'message' => __('Form submitted successfully', 'modularity-frontend-form')
        ]);
    }
}