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
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\JsonDotHydrator;
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\MultipartFormDataEncoder;
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\UploadedFileSnapshots;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WP_Error;
use WP_REST_Request;

class WebHookHandler implements HandlerInterface
{
    use GetModuleConfigInstanceTrait;

    private bool $transportAttempted = false;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private HandlerResultInterface $handlerResult = new HandlerResult(),
        private LoggerInterface $logger = new NullLogger,
        private ?UploadedFileSnapshots $uploadedFileSnapshots = null
    ) {}

    public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
    {
        $this->transportAttempted = false;
        try {
            $config = $this->moduleConfigInstance->getWebHookHandlerConfig();
            if (!is_string($config?->callbackUrl ?? null) || trim($config->callbackUrl) === '') {
                $this->error($this->wpService->__('Webhook configuration requires a callback URL.', 'modularity-frontend-form'));
                return $this->handlerResult;
            }
            $format = $config->requestFormat ?? 'json';
            if (!in_array($format, ['json', 'multipart'], true)) {
                $this->error($this->wpService->__('Unsupported webhook request format. Select JSON or Multipart (image support).', 'modularity-frontend-form'));
                return $this->handlerResult;
            }
            if ($format === 'multipart') {
                $this->sendMultipartRequest($config, $data, $request);
            } else {
                $body = $this->createBody($data, $config);
                $this->sendRequest($config, $body ? json_encode($body) : null, $this->createHeaders($data, $config));
            }
            return $this->handlerResult;
        } finally {
            if ($this->uploadedFileSnapshots !== null && !$this->uploadedFileSnapshots->cleanup()) {
                $this->error($this->transportAttempted
                    ? $this->wpService->__('Webhook snapshot cleanup failed after transport. The remote operation may have succeeded.', 'modularity-frontend-form')
                    : $this->wpService->__('Webhook snapshot cleanup failed. The submission was not sent.', 'modularity-frontend-form'));
            }
        }
    }

    private function sendMultipartRequest(object $config, array $data, WP_REST_Request $request): void
    {
        try {
            $this->uploadedFileSnapshots ??= new UploadedFileSnapshots(
                $request->get_file_params()[$this->config->getFieldNamespace()] ?? [],
                $this->acfService,
                $config->body ?? '{}',
                $this->moduleConfigInstance->getFieldKeysRegisteredAsFormFields() ?? []
            );
            $formData = $this->replaceAcfIdsWithNames(
                $this->normalizeAcfFormData($data[$this->config->getFieldNamespace()] ?? $data)
            );
            $payload = $this->uploadedFileSnapshots->nativePayload($formData);
            $limit = $this->wpService->wpMaxUploadSize();
            $encoded = (new MultipartFormDataEncoder())->encode(
                $payload['values'],
                $payload['files'],
                is_numeric($limit) && (int) $limit > 0 ? min((int) $limit, 8388608) : 8388608
            );
        } catch (\Throwable) {
            $this->error($this->wpService->__('Webhook mapping, image preparation or payload limits failed. The submission was not sent.', 'modularity-frontend-form'));
            return;
        }
        try {
            $this->sendRequest($config, $encoded['body'], $this->createMultipartHeaders($config, $encoded['contentType']), true);
        } catch (\Throwable) {
            $this->error($this->wpService->__('Webhook transport failed. The remote operation may have succeeded.', 'modularity-frontend-form'));
        }
    }

    private function sendRequest(object $config, ?string $body, array $headers, bool $multipart = false): void
    {
        $this->transportAttempted = true;
        $response = $this->wpService->wpRemotePost($config->callbackUrl, [
            'body' => $body,
            'timeout' => max(1, min(120, isset($config->timeout) ? (int) $config->timeout : 20)),
            'headers' => $headers,
        ]);
        if ($this->wpService->isWpError($response)) {
            $this->error($multipart
                ? $this->wpService->__('Webhook transport failed. The remote operation may have succeeded.', 'modularity-frontend-form')
                : $response->get_error_message());
            return;
        }
        $status = (int) $this->wpService->wpRemoteRetrieveResponseCode($response);
        if ($status < 200 || $status >= 300) {
            $this->handlerResult->setError(new WP_Error(
                RestApiResponseStatusEnums::HandlerError->value,
                sprintf($multipart
                    ? $this->wpService->__('Webhook request failed with HTTP status %d. The remote operation may have succeeded.', 'modularity-frontend-form')
                    : $this->wpService->__('Webhook request failed. The server responded with HTTP status %d.', 'modularity-frontend-form'), $status),
                ['status' => $status]
            ));
        }
    }

    private function error(?string $message): void
    {
        $this->handlerResult->setError(new WP_Error(RestApiResponseStatusEnums::HandlerError->value, $message));
    }

    private function createBody(array $data, object $config): array|null
    {
        if (empty($config->body)) return null;

        $formData = $this->replaceAcfIdsWithNames(
            $this->normalizeAcfFormData($data[$this->config->getFieldNamespace()] ?? $data)
        );

        $formData['*'] = $formData;

        $this->logger->debug('Top level keys in normalized FormData: {data}', ['data' => array_keys($formData)]);

        $hydrator = new JsonDotHydrator();
        $formDataAsJson = $hydrator->hydrate($config->body, $formData);
        return \json_decode($formDataAsJson, true);
    }

    private function createHeaders(array $_data, object $config): array
    {
        $headers = ['Content-Type' => 'application/json'];
        foreach (is_array($config->headers ?? null) ? $config->headers : [] as $header) {
            $name = is_array($header) ? ($header['header'] ?? null) : null;
            if (!is_string($name) || strtolower(trim($name)) === 'x-acf-rest-upload') {
                continue;
            }
            $headers[$name] = is_scalar($header['value'] ?? null) ? (string) $header['value'] : '';
        }
        return $headers;
    }

    private function createMultipartHeaders(object $config, string $contentType): array
    {
        $headers = [];
        foreach (is_array($config->headers ?? null) ? $config->headers : [] as $header) {
            $name = is_array($header) ? ($header['header'] ?? null) : null;
            if (!is_string($name)) {
                continue;
            }
            $name = trim($name);
            if ($name === '' || in_array(strtolower($name), ['content-type', 'x-acf-rest-upload'], true)) {
                continue;
            }
            $value = $header['value'] ?? '';
            $headers[$name] = is_scalar($value) ? (string) $value : '';
        }
        $headers['Content-Type'] = $contentType;
        $headers['X-ACF-Rest-Upload'] = 'true';
        return $headers;
    }

    private function normalizeAcfFormData(array $formData): array
    {
        $normalizersByType = [
            'true_false'  => fn($v) => (bool)(int) $v,
            'google_map'  => function ($value) {
                $value = is_string($value) ? json_decode($value, true) : $value;
                return is_array($value) ? $value : null;
            },
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
                $newKey = is_array($fieldObj) ? ($fieldObj['name'] ?? $key) : $key;
            }
            if (is_array($value)) {
                $value = $this->replaceAcfIdsWithNames($value);
            }
            $result[$newKey] = $value;
        }
        return $result;
    }
}
