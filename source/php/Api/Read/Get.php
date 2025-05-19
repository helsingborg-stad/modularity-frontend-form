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

use function AcfService\Implementations\get_fields;

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
              //RestApiParamEnums::ModuleId,
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
        //Get fields from post id 
        $postId          = $request->get_params()['post-id']                            ?? null;
        $post            = $this->wpService->getPost($postId);
        

        var_dump(get_fields($postId, true, false));

        return new WP_REST_Response(
            [
                'status' => RestApiResponseStatusEnums::Success,
                'data'   => $post
            ],
            WP_Http::OK
        );
    }
}