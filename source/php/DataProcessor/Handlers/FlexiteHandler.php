<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use WpService\WpService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_Error;
use WP_REST_Request;

class FlexiteHandler implements HandlerInterface
{
    public function __construct(
        private WpService $wpService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private HandlerResultInterface $handlerResult = new HandlerResult()
    ) {}

    /**
     * Handle the data
     */
    public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
    {
        $config = $this->moduleConfigInstance->getFlexiteHandlerConfig();

        if (!$this->validateConfig($config)) {
            return $this->handlerResult;
        }

        $endpoint = rtrim($config->baseUrl, '/')
            . '/process/' . urlencode($config->processId) . '/instance';

        $response = $this->wpService->wpRemotePost($endpoint, [
            'timeout' => 20,
            'headers' => [
                'Authorization' => 'Bearer ' . $config->token,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode($this->mapPayload($data)),
        ]);

        if ($this->wpService->isWpError($response)) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $this->wpService->__('Failed to create Flexite process instance.', 'modularity-frontend-form')
                )
            );
        }

        return $this->handlerResult;
    }

    /**
     * Validate required Flexite config
     */
    private function validateConfig(object $config): bool
    {
        if (empty($config->baseUrl) || empty($config->processId) || empty($config->token)) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $this->wpService->__('Flexite configuration is incomplete.', 'modularity-frontend-form')
                )
            );
            return false;
        }

        if (!filter_var($config->baseUrl, FILTER_VALIDATE_URL)) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $this->wpService->__('Invalid Flexite base URL.', 'modularity-frontend-form')
                )
            );
            return false;
        }

        return true;
    }

    /**
     * Map frontend form data → Flexite payload
     *
     * Flexite example:
     * {
     *   "name": "New Process Instance",
     *   "attributes": { ... }
     * }
     */
    private function mapPayload(array $data): array
    {
        return [
            'name'       => $data['title'] ?? 'Frontend Form Submission',
            'attributes' => $data,
        ];
    }
}