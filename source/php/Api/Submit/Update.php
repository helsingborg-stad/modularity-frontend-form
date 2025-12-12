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
use ModularityFrontendForm\DataProcessor\Handlers\HandlerFactory;
use ModularityFrontendForm\DataProcessor\Handlers\NullHandler;

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
        return $this->wpService->registerRestRoute(self::NAMESPACE, self::ROUTE, array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => '__return_true',
            'args' => (
              new RestApiParams($this->wpService, $this->config, $this->moduleConfigFactory)
            )->getParamSpecification(
                RestApiParamEnums::ModuleId,
                RestApiParamEnums::Nonce,
                RestApiParamEnums::PostId,
                RestApiParamEnums::HoldingPostId,
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
        $params = (new RestApiParams(
            $this->wpService, 
            $this->config, 
            $this->moduleConfigFactory)
        )->getValuesFromRequest($request);

        // Data to be submitted
        $data = $request->get_params()[$this->config->getFieldNamespace()]   ?? null;

        // Handler factories
        $validatorFactory   = new ValidatorFactory($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory);
        $handlerFactory     = new HandlerFactory($this->wpService, $this->acfService, $this->config, $this->moduleConfigFactory);

        // Creates the data processor
        $dataProcessor = new DataProcessor(
            $validatorFactory->createInsertValidators($params->moduleId),
            $handlerFactory->createHandlers($params, $request),
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
            'message' => __('Form updated successfully', 'modularity-frontend-form')
        ]);
    }
}