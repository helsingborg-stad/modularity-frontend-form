<?php

namespace ModularityFrontendForm\Api\Submit;

use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiEndpoint;
use \ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\DataProcessor;
use ModularityFrontendForm\Api\RestApiParams;
use ModularityFrontendForm\Api\RestApiParamEnums;
use WP;
use WP_Error;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WpService\WpService;

use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use ModularityFrontendForm\DataProcessor\Validators\ValidatorFactory;

class Update extends RestApiEndpoint
{
    use GetModuleConfigInstanceTrait;

    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'submit/update';
    public const KEY       = 'updateForm';

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
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => '__return_true',
            'args' => (
              new RestApiParams($this->wpService, $this->moduleConfigFactory)
            )->getParamSpecification(
              RestApiParamEnums::ModuleId,
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
        $moduleId        = $request->get_params()['module-id']                          ?? null;
        $fieldMeta       = $request->get_params()[$this->config->getFieldNamespace()]   ?? null;
        $nonce           = $request->get_params()['nonce']                              ?? '';
        $postId          = $request->get_params()['post-id']                            ?? null;

        //Get validators
        $validators = (function () use ($moduleId) {
            $validatorFactory = new ValidatorFactory($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory);
            return $validatorFactory->createUpdateValidators($moduleId) ?? [];
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
            return $this->wpService->restEnsureResponse([
                'status' => RestApiResponseStatusEnums::GenericError,
                'message' => __('Something went wrong when updating the form.', 'modularity-frontend-form'),
                'errors' => $result,
            ]);
        }

        return $this->wpService->restEnsureResponse([
            'status' => RestApiResponseStatusEnums::Success,
            'message' => __('Form updated successfully', 'modularity-frontend-form')
        ]);
    }
}