<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use ModularityFrontendForm\DataProcessor\FileHandlers\NullFileHandler;
use ModularityFrontendForm\DataProcessor\FileHandlers\FileHandlerInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\JsonDotHydrator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WP_Error;
use WP_REST_Request;

class WebHookHandler implements HandlerInterface
{

    use GetModuleConfigInstanceTrait;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private object $params,
        private HandlerResultInterface $handlerResult = new HandlerResult(),
        private LoggerInterface $logger = new NullLogger,
        private ?FileHandlerInterface $fileHandler = null
    ) {
        if ($this->fileHandler === null) {
            $this->fileHandler = new NullFileHandler($this->config, $this->moduleConfigInstance, $this->wpService);
        }
    }

    /**
     * Handle the data
     *
     * @param array $data The data to handle
     * @return HandlerResultInterface|null The result of the handling
     */
    public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
    {
        $config = $this->moduleConfigInstance->getWebHookHandlerConfig();
        $this->trySendRequest(
            $config->callbackUrl,
            $this->createBody($data, $config),
            $this->createHeaders($data, $config)
        );

        return $this->handlerResult;
    }

    /**
     * Send the request to the webhook URL
     *
     * @param string $url The URL to send the request to
     * @param array $data The data to send in the request
     * @return bool True if the request was sent successfully, false otherwise
     */
    private function trySendRequest(string $url, array|null $data, array $headers = []): bool
    {
        $response = $this->wpService->wpRemotePost($url, [
            'body' => $data ? \json_encode($data) : null,
            'timeout' => 20,
            'headers' => $headers
        ]);

        if ($this->wpService->isWpError($response)) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $response->get_error_message(),
                    ['response' => $response],
                )
            );

            return false;
        }

        return true;
    }

    private function createBody(array $data, object $config): array|null
    {
        if (empty($config->body)) return null;

        $formData = $this->replaceAcfIdsWithNames(
            $this->normalizeAcfFormData($data['mod-frontend-form'])
        );

        $formData['*'] = $formData;

        $this->logger->debug('Top level keys in normalized FormData: {data}', ['data' => array_keys($formData)]);

        $hydrator = new JsonDotHydrator();
        $formDataAsJson = $hydrator->hydrate($config->body, $formData);
        return \json_decode($formDataAsJson, true);
    }

    private function createHeaders(array $_data, object $config): array
    {
        return [
            ...['Content-Type' => 'application/json',],
            ...array_column($config->headers ?? [], 'value', 'header')
        ];
    }

    private function normalizeAcfFormData(array $formData): array
    {
        $normalizersByType = [
            'true_false'  => fn($v) => (bool)(int) $v,
            'null'        => fn($v) => $v,
            'repeater'    => fn($arr) => array_map(fn($v) => $this->normalizeAcfFormData($v), $arr ?? []),
        ];

        $fieldObjects = array_map(
            fn($fieldId) => $this->acfService->getFieldObject($fieldId),
            array_combine(array_keys($formData), array_keys($formData))
        );

        foreach ($fieldObjects as $fieldId => $fieldObject) {
            $fieldType = $fieldObject['type'] ?? 'null';
            $hasNormalizer = fn($t) => in_array($t, array_keys($normalizersByType));
            $normalizeFn = $hasNormalizer($fieldType) ? $normalizersByType[$fieldType] : $normalizersByType['null'];
            $formData[$fieldId] = $normalizeFn($formData[$fieldId]);
        }
        
        return $formData;
    }

    private function replaceAcfIdsWithNames(array $formData): array
    {
        $result = [];

        foreach ($formData as $key => $value) {
            $newKey = $key;
            if (is_string($key) && str_starts_with($key, 'field_')) {
                $fieldObj = $this->acfService->getFieldObject($key);
                $newKey = is_array($fieldObj)
                    ? ($fieldObj['name'] ?? $key)
                    : $key;
            }

            if (is_array($value)) {
                $value = $this->replaceAcfIdsWithNames($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }
}
