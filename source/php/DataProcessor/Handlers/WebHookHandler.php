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
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\MultipartFormDataEncoder;
use ModularityFrontendForm\DataProcessor\Handlers\Webhook\UploadedFileSnapshots;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WP_Error;
use WP_REST_Request;

class WebHookHandler implements HandlerInterface
{

    use GetModuleConfigInstanceTrait;

    private const REQUEST_FORMAT_MULTIPART = 'multipart';

    private const DEFAULT_TIMEOUT     = 20;
    private const MIN_TIMEOUT         = 1;
    private const MAX_TIMEOUT         = 120;
    private const MAX_MULTIPART_RETRY = 2;

    private const IDEMPOTENCY_HEADER     = 'Idempotency-Key';
    private const PROTOCOL_VERSION_HEADER = 'X-ACF-Rest-Upload-Version';
    private const PROTOCOL_VERSION        = '1';

    private const BASE_RETRY_DELAY = 0.5;
    private const MAX_RETRY_DELAY  = 30.0;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private object $params,
        private HandlerResultInterface $handlerResult = new HandlerResult(),
        private LoggerInterface $logger = new NullLogger,
        private ?FileHandlerInterface $fileHandler = null,
        private ?UploadedFileSnapshots $uploadedFileSnapshots = null
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
        try {
            $config = $this->moduleConfigInstance->getWebHookHandlerConfig();

            if (!is_string($config?->callbackUrl ?? null) || trim($config->callbackUrl) === '') {
                $this->handlerResult->setError(
                    new WP_Error(
                        RestApiResponseStatusEnums::HandlerError->value,
                        __('Webhook configuration requires a callback URL.', 'modularity-frontend-form')
                    )
                );

                return $this->handlerResult;
            }

            if (($config->requestFormat ?? 'json') === self::REQUEST_FORMAT_MULTIPART) {
                $this->sendMultipartRequest($config, $data);
            } else {
                $this->sendJsonRequest($config, $data);
            }

            return $this->handlerResult;
        } finally {
            $this->uploadedFileSnapshots?->cleanup();
        }
    }

    private function sendJsonRequest(object $config, array $data): void
    {
        $body = $this->createBody($data, $config);

        $this->sendRequest(
            $config->callbackUrl,
            $body ? \json_encode($body) : null,
            $this->createHeaders($data, $config),
            $this->resolveTimeout($config),
            false
        );
    }

    private function sendMultipartRequest(object $config, array $data): void
    {
        $payload = $this->createMultipartPayload($data, $config);
        $files   = $this->uploadedFileSnapshots?->getFileMap() ?? [];

        if (!$this->withinUploadLimit($payload, $files)) {
            return;
        }

        try {
            $encoded = (new MultipartFormDataEncoder())->encode($payload, $files);
        } catch (InvalidArgumentException $exception) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $exception->getMessage()
                )
            );

            return;
        }

        $this->sendRequest(
            $config->callbackUrl,
            $encoded['body'],
            $this->createMultipartHeaders($config, $encoded['contentType']),
            $this->resolveTimeout($config),
            true
        );
    }

    /**
     * Compare the aggregate size of the files referenced by the hydrated
     * payload against the origin WordPress upload limit.
     *
     * @param array<string, mixed> $payload
     * @param array<string, array{name:string, type:string, tmp_name:string, size:int}> $files
     */
    private function withinUploadLimit(array $payload, array $files): bool
    {
        if ($files === []) {
            return true;
        }

        $limit = $this->wpService->wpMaxUploadSize();
        if (!is_numeric($limit) || (int) $limit <= 0) {
            return true;
        }

        $total = 0;
        foreach ($this->collectReferencedFileKeys($payload) as $key) {
            if (isset($files[$key]['size'])) {
                $total += (int) $files[$key]['size'];
            }
        }

        if ($total <= (int) $limit) {
            return true;
        }

        $this->handlerResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::HandlerError->value,
                sprintf(
                    /* translators: 1: selected upload size, 2: maximum upload size. */
                    __('The selected uploads (%1$s) exceed the maximum upload size of %2$s.', 'modularity-frontend-form'),
                    size_format($total),
                    size_format((int) $limit)
                )
            )
        );

        return false;
    }

    /**
     * Collect the file keys referenced by `$file:<key>` values in the payload.
     *
     * @param array<string, mixed> $payload
     * @return array<int, string>
     */
    private function collectReferencedFileKeys(array $payload): array
    {
        $keys = [];

        $walk = static function (mixed $value) use (&$walk, &$keys): void {
            if (is_array($value)) {
                foreach ($value as $child) {
                    $walk($child);
                }

                return;
            }

            if (
                is_string($value)
                && str_starts_with($value, MultipartFormDataEncoder::FILE_REFERENCE_PREFIX)
            ) {
                $key = substr($value, strlen(MultipartFormDataEncoder::FILE_REFERENCE_PREFIX));
                if ($key !== '') {
                    $keys[$key] = true;
                }
            }
        };

        $walk($payload);

        return array_keys($keys);
    }

    /**
     * Send the request to the webhook URL.
     *
     * @param string      $url    The URL to send the request to.
     * @param string|null $body   The raw, already encoded request body.
     * @param array       $headers The request headers.
     * @param int         $timeout The request timeout in seconds.
     * @param bool        $allowRetry Whether transient failures may be retried.
     */
    private function sendRequest(
        string $url,
        ?string $body,
        array $headers,
        int $timeout,
        bool $allowRetry
    ): void {
        $maxRetries = $allowRetry ? self::MAX_MULTIPART_RETRY : 0;
        $attempt    = 0;

        while (true) {
            $response = $this->wpService->wpRemotePost($url, [
                'body'    => $body,
                'timeout' => $timeout,
                'headers' => $headers,
            ]);

            if ($this->wpService->isWpError($response)) {
                if ($attempt < $maxRetries) {
                    $attempt++;
                    $this->sleepBeforeRetry($attempt, null);
                    continue;
                }

                $this->handlerResult->setError(
                    new WP_Error(
                        RestApiResponseStatusEnums::HandlerError->value,
                        $response->get_error_message(),
                        ['response' => $response],
                    )
                );

                return;
            }

            $statusCode = (int) $this->wpService->wpRemoteRetrieveResponseCode($response);
            $isFailure  = $statusCode < 200 || $statusCode >= 300;

            if ($isFailure && $attempt < $maxRetries && $this->isRetryableStatus($statusCode)) {
                $attempt++;
                $this->sleepBeforeRetry(
                    $attempt,
                    $this->resolveRetryAfterHeader($response)
                );
                continue;
            }

            if ($isFailure) {
                $this->handlerResult->setError(
                    new WP_Error(
                        RestApiResponseStatusEnums::HandlerError->value,
                        sprintf(
                            /* translators: %d: HTTP status code returned by the webhook. */
                            __('Webhook request failed. The server responded with HTTP status %d.', 'modularity-frontend-form'),
                            $statusCode
                        ),
                        ['status' => $statusCode, 'response' => $response]
                    )
                );
            }

            return;
        }
    }

    private function isRetryableStatus(int $statusCode): bool
    {
        return $statusCode === 408
            || $statusCode === 425
            || $statusCode === 429
            || $statusCode >= 500;
    }

    /**
     * Resolve the Retry-After header from a response. WpService may expose the
     * headers as either a case-insensitive dictionary or a plain array, and a
     * header may hold multiple values, so normalize all shapes to a string.
     *
     * @param array|\WP_Error $response
     */
    private function resolveRetryAfterHeader(array|WP_Error $response): ?string
    {
        $headers = $this->wpService->wpRemoteRetrieveHeaders($response);

        if ($headers instanceof \ArrayAccess) {
            $value = $headers['retry-after'] ?? null;
        } elseif (is_array($headers)) {
            $value = null;
            foreach ($headers as $name => $headerValue) {
                if (strtolower((string) $name) === 'retry-after') {
                    $value = $headerValue;
                    break;
                }
            }
        } else {
            $value = null;
        }

        if (is_array($value)) {
            $value = reset($value);
        }

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function sleepBeforeRetry(int $attempt, ?string $retryAfter): void
    {
        $seconds = $this->resolveRetryDelay($attempt, $retryAfter);

        if ($seconds > 0) {
            \usleep((int) round($seconds * 1_000_000));
        }
    }

    private function resolveRetryDelay(int $attempt, ?string $retryAfter): float
    {
        if (is_string($retryAfter) && trim($retryAfter) !== '') {
            $retryAfter = trim($retryAfter);

            if (is_numeric($retryAfter)) {
                return max(0.0, min((float) $retryAfter, self::MAX_RETRY_DELAY));
            }

            $timestamp = strtotime($retryAfter);
            if ($timestamp !== false) {
                return max(0.0, min((float) ($timestamp - time()), self::MAX_RETRY_DELAY));
            }
        }

        $delay = self::BASE_RETRY_DELAY * (2 ** max(0, $attempt - 1));

        return max(0.0, min($delay, self::MAX_RETRY_DELAY));
    }

    private function resolveTimeout(object $config): int
    {
        $timeout = isset($config->timeout) ? (int) $config->timeout : self::DEFAULT_TIMEOUT;

        return max(self::MIN_TIMEOUT, min(self::MAX_TIMEOUT, $timeout));
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

    private function createMultipartPayload(array $data, object $config): array
    {
        $formData = $this->replaceAcfIdsWithNames(
            $this->normalizeAcfFormData($data[$this->config->getFieldNamespace()] ?? $data)
        );
        $formData = $this->addFileReferences($formData);
        $formData['*'] = $formData;

        if (empty($config->body)) {
            return [];
        }

        $formDataAsJson = (new JsonDotHydrator())->hydrate($config->body, $formData);
        $payload        = \json_decode($formDataAsJson, true);

        return is_array($payload) ? $payload : [];
    }

    private function addFileReferences(array $formData): array
    {
        if ($this->uploadedFileSnapshots === null) {
            return $formData;
        }

        foreach ($this->uploadedFileSnapshots->getReferences() as $key => $reference) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $formData[$key] = $reference;
        }

        return $formData;
    }

    private function createHeaders(array $_data, object $config): array
    {
        return [
            ...['Content-Type' => 'application/json',],
            ...array_column(is_array($config->headers ?? null) ? $config->headers : [], 'value', 'header')
        ];
    }

    private function createMultipartHeaders(object $config, string $contentType): array
    {
        $reserved = [
            'content-type',
            'idempotency-key',
            strtolower(self::PROTOCOL_VERSION_HEADER),
        ];

        $headers = [];
        foreach (is_array($config->headers ?? null) ? $config->headers : [] as $header) {
            $name = is_array($header) ? ($header['header'] ?? null) : null;
            if (!is_string($name) || $name === '' || in_array(strtolower($name), $reserved, true)) {
                continue;
            }

            $headers[$name] = $header['value'] ?? '';
        }

        // Multipart controls these headers itself, overriding any configured value.
        $headers['Content-Type']                       = $contentType;
        $headers[self::IDEMPOTENCY_HEADER]             = $this->generateUuid();
        $headers[self::PROTOCOL_VERSION_HEADER]        = self::PROTOCOL_VERSION;

        return $headers;
    }

    private function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
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
