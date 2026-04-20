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

/**
 * Handler that forwards submitted form data to a Flexite process instance.
 *
 * The implementation follows the Flexite REST API OpenAPI schema bundled with
 * the plugin at `docs/swagger/flexite.json`. It supports the most common
 * operations exposed by that schema:
 *
 *  - createInstance         POST   /process/{process_id}/instance
 *  - updateInstance         PATCH  /process/{process_id}/instance/{instance_id}
 *  - signInstance           POST   /process/{process_id}/instance/{instance_id}
 *  - getInstanceData        GET    /process/{process_id}/instance/{instance_id}
 *  - getMessageReceivers    GET    /process/{process_id}/instance/{instance_id}/message
 *  - addMessage             POST   /process/{process_id}/instance/{instance_id}/message
 *  - replyMessage           POST   /process/{process_id}/instance/{instance_id}/message/{message_id}
 *  - getIdentityProcesses   GET    /identity/processes
 *  - getIdentityInstances   POST   /identity/instances
 *
 * Authentication is performed via the header based scheme declared in the
 * schema: `SECRET_KEY`, `LOGIN_TYPE`, `USERNAME`, `PASSWORD` and
 * `Accept-Language`. The `PASSWORD` header is expected to already be a
 * Base64-encoded SHA1 hash of the user's password.
 */
class FlexiteHandler implements HandlerInterface
{
    /**
     * Default `Accept-Language` header used when the config does not provide one.
     */
    private const DEFAULT_ACCEPT_LANGUAGE = 'en-US, en;q=0.9';

    /**
     * Default request timeout in seconds.
     */
    private const DEFAULT_TIMEOUT = 20;

    /**
     * Allowed Flexite value types per the `Value.type` enum.
     *
     * @var string[]
     */
    private const VALUE_TYPES = ['STRING', 'NUMBER', 'DATE', 'TIME', 'FILE', 'URL'];

    public function __construct(
        private WpService $wpService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private HandlerResultInterface $handlerResult = new HandlerResult()
    ) {}

    /**
     * Handle the form submission by creating a new Flexite process instance.
     *
     * @param array            $data    Submitted form data.
     * @param WP_REST_Request  $request Current REST request.
     *
     * @return HandlerResultInterface|null
     */
    public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
    {
        $config = $this->moduleConfigInstance->getFlexiteHandlerConfig();

        if (!$this->validateConfig($config)) {
            return $this->handlerResult;
        }

        $payload = $this->mapPayload($data, $config);
        $result  = $this->createInstance($config, $payload);

        if ($result instanceof WP_Error) {
            $this->handlerResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::HandlerError->value,
                    $result->get_error_message()
                )
            );
        }

        return $this->handlerResult;
    }

    /**
     * Create a new process instance.
     *
     * @param object $config  Flexite handler config (baseUrl, processId, auth headers, ...).
     * @param array  $payload `InstanceRequest` body: `['components' => [...]]`.
     *
     * @return array|WP_Error Decoded `InstanceResponse` on success, `WP_Error` on failure.
     */
    public function createInstance(object $config, array $payload): array|WP_Error
    {
        return $this->request(
            'POST',
            $this->buildInstancesUrl($config),
            $config,
            $payload
        );
    }

    /**
     * Update an existing process instance.
     *
     * @param object $config     Flexite handler config.
     * @param int    $instanceId Instance ID to update.
     * @param array  $payload    `InstanceRequest` body.
     *
     * @return array|WP_Error Decoded response on success, `WP_Error` on failure.
     */
    public function updateInstance(object $config, int $instanceId, array $payload): array|WP_Error
    {
        return $this->request(
            'PATCH',
            $this->buildInstanceUrl($config, $instanceId),
            $config,
            $payload
        );
    }

    /**
     * Sign an existing process instance.
     *
     * @param object $config     Flexite handler config.
     * @param int    $instanceId Instance ID to sign.
     * @param array  $payload    `InstanceRequest` body.
     *
     * @return array|WP_Error
     */
    public function signInstance(object $config, int $instanceId, array $payload): array|WP_Error
    {
        return $this->request(
            'POST',
            $this->buildInstanceUrl($config, $instanceId),
            $config,
            $payload
        );
    }

    /**
     * Retrieve detailed data for an existing instance.
     *
     * @return array|WP_Error Decoded `InstanceDetailsResponse` on success.
     */
    public function getInstanceData(object $config, int $instanceId): array|WP_Error
    {
        return $this->request(
            'GET',
            $this->buildInstanceUrl($config, $instanceId),
            $config
        );
    }

    /**
     * Retrieve the list of available message receivers for an instance.
     *
     * @return array|WP_Error Decoded `MessageReceiversResponse` on success.
     */
    public function getMessageReceivers(object $config, int $instanceId): array|WP_Error
    {
        return $this->request(
            'GET',
            $this->buildInstanceUrl($config, $instanceId) . '/message',
            $config
        );
    }

    /**
     * Add a new message to an existing instance.
     *
     * @param array $message `NewMessageRequest` body.
     *
     * @return array|WP_Error Decoded `MessageResponse` on success.
     */
    public function addMessage(object $config, int $instanceId, array $message): array|WP_Error
    {
        return $this->request(
            'POST',
            $this->buildInstanceUrl($config, $instanceId) . '/message',
            $config,
            $message
        );
    }

    /**
     * Reply to an existing message.
     *
     * @param array $message `ReplyMessageRequest` body.
     *
     * @return array|WP_Error Decoded `MessageResponse` on success.
     */
    public function replyMessage(object $config, int $instanceId, int $messageId, array $message): array|WP_Error
    {
        return $this->request(
            'POST',
            $this->buildInstanceUrl($config, $instanceId) . '/message/' . urlencode((string) $messageId),
            $config,
            $message
        );
    }

    /**
     * List processes and e-services available to the authenticated identity.
     *
     * @return array|WP_Error Decoded `IdentityProcessResponse` on success.
     */
    public function getIdentityProcesses(object $config): array|WP_Error
    {
        return $this->request(
            'GET',
            rtrim($config->baseUrl, '/') . '/identity/processes',
            $config
        );
    }

    /**
     * List instances available to the authenticated identity.
     *
     * @param array $payload `IdentityListRequest` body.
     *
     * @return array|WP_Error Decoded `IdentityListResponse` on success.
     */
    public function getIdentityInstances(object $config, array $payload): array|WP_Error
    {
        return $this->request(
            'POST',
            rtrim($config->baseUrl, '/') . '/identity/instances',
            $config,
            $payload
        );
    }

    /**
     * Validate required Flexite configuration.
     *
     * @param object|null $config Flexite handler config.
     */
    private function validateConfig(?object $config): bool
    {
        if (!is_object($config)) {
            $this->setHandlerError('Flexite configuration is missing.');
            return false;
        }

        $required = ['baseUrl', 'processId', 'secretKey', 'loginType', 'username', 'password'];
        foreach ($required as $field) {
            if (empty($config->$field ?? null)) {
                $this->setHandlerError('Flexite configuration is incomplete.');
                return false;
            }
        }

        if (!filter_var($config->baseUrl, FILTER_VALIDATE_URL)) {
            $this->setHandlerError('Invalid Flexite base URL.');
            return false;
        }

        return true;
    }

    /**
     * Map submitted form data to a Flexite `InstanceRequest`.
     *
     * Three input shapes are supported, in order of precedence:
     *  1. `$data['components']` is already a list of components → used as-is
     *     (after light normalisation).
     *  2. `$config->componentMap` provides a `fieldKey => { id, type, caption }`
     *     mapping that is applied to the flat form data.
     *  3. Fallback: best effort, build a single component per scalar key using
     *     the key as `caption` and `STRING` as value type.
     *
     * @param array  $data   Submitted form data.
     * @param object $config Flexite handler config.
     *
     * @return array `InstanceRequest` body.
     */
    private function mapPayload(array $data, object $config): array
    {
        if (isset($data['components']) && is_array($data['components'])) {
            return ['components' => $this->normalizeComponents($data['components'])];
        }

        $componentMap = $config->componentMap ?? null;
        if (is_array($componentMap) && $componentMap !== []) {
            return ['components' => $this->applyComponentMap($data, $componentMap)];
        }

        return ['components' => $this->fallbackComponents($data)];
    }

    /**
     * Normalise a pre-built list of components so that each matches the
     * Flexite `Component` / `Value` schema.
     *
     * @param array $components Raw component definitions.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeComponents(array $components): array
    {
        $normalized = [];
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $normalizedComponent = [];
            if (isset($component['id'])) {
                $normalizedComponent['id'] = (int) $component['id'];
            }
            if (isset($component['caption']) && is_string($component['caption'])) {
                $normalizedComponent['caption'] = $component['caption'];
            }
            if (isset($component['type'])) {
                $normalizedComponent['type'] = (string) $component['type'];
            }
            if (isset($component['values']) && is_array($component['values'])) {
                $normalizedComponent['values'] = $this->normalizeValues($component['values']);
            }
            $normalized[] = $normalizedComponent;
        }
        return $normalized;
    }

    /**
     * Normalise component values so they conform to the `Value` schema.
     *
     * @param array $values Raw value definitions.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeValues(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (!is_array($value)) {
                $normalized[] = ['type' => 'STRING', 'value' => (string) $value];
                continue;
            }
            $normalized[] = $this->buildValue($value);
        }
        return $normalized;
    }

    /**
     * Build a single Flexite `Value` object from an associative array.
     *
     * Supported keys: `id`, `name`, `type`, `value`, `filename`, `content`.
     * A `content` key is interpreted as raw binary file content that will be
     * base64 encoded. A pre-encoded base64 string should be passed in `value`
     * directly together with `type = FILE`.
     *
     * @param array $value Value definition.
     *
     * @return array<string, mixed>
     */
    private function buildValue(array $value): array
    {
        $type = isset($value['type']) ? strtoupper((string) $value['type']) : 'STRING';
        if (!in_array($type, self::VALUE_TYPES, true)) {
            $type = 'STRING';
        }

        $built = ['type' => $type];

        if (isset($value['id'])) {
            $built['id'] = (int) $value['id'];
        }
        if (isset($value['name']) && is_string($value['name'])) {
            $built['name'] = $value['name'];
        }
        if (isset($value['filename']) && is_string($value['filename'])) {
            $built['filename'] = $value['filename'];
        }

        if ($type === 'FILE' && isset($value['content']) && is_string($value['content'])) {
            $built['value'] = base64_encode($value['content']);
        } elseif (array_key_exists('value', $value)) {
            $built['value'] = is_scalar($value['value']) ? (string) $value['value'] : '';
        }

        return $built;
    }

    /**
     * Build components from a flat form data array using a configured mapping.
     *
     * The mapping is expected to be keyed by form field name with each entry
     * being either a component ID (int/string) or an object/array describing a
     * component (`id`, `type`, `caption`, `valueType`).
     *
     * @param array                                                       $data
     * @param array<string, int|string|array<string, mixed>|object>       $componentMap
     *
     * @return array<int, array<string, mixed>>
     */
    private function applyComponentMap(array $data, array $componentMap): array
    {
        $components = [];
        foreach ($componentMap as $field => $mapping) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $mapping = is_object($mapping) ? (array) $mapping : $mapping;
            if (is_int($mapping) || is_string($mapping)) {
                $mapping = ['id' => (int) $mapping];
            }
            if (!is_array($mapping) || !isset($mapping['id'])) {
                continue;
            }

            $valueType = isset($mapping['valueType']) ? strtoupper((string) $mapping['valueType']) : 'STRING';
            if (!in_array($valueType, self::VALUE_TYPES, true)) {
                $valueType = 'STRING';
            }

            $component = [
                'id'     => (int) $mapping['id'],
                'values' => $this->buildValuesFromFieldData($data[$field], $valueType),
            ];
            if (isset($mapping['caption']) && is_string($mapping['caption'])) {
                $component['caption'] = $mapping['caption'];
            }
            if (isset($mapping['type'])) {
                $component['type'] = (string) $mapping['type'];
            }

            $components[] = $component;
        }
        return $components;
    }

    /**
     * Build `Value` entries from a single form field's raw data.
     *
     * @param mixed  $fieldData Raw value(s) from the submitted form.
     * @param string $valueType One of {@see self::VALUE_TYPES}.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildValuesFromFieldData(mixed $fieldData, string $valueType): array
    {
        if (is_array($fieldData) && $this->isList($fieldData)) {
            $values = [];
            foreach ($fieldData as $item) {
                $values[] = $this->buildValue(is_array($item) ? $item + ['type' => $valueType] : ['type' => $valueType, 'value' => $item]);
            }
            return $values;
        }

        if (is_array($fieldData)) {
            return [$this->buildValue($fieldData + ['type' => $valueType])];
        }

        return [$this->buildValue(['type' => $valueType, 'value' => $fieldData])];
    }

    /**
     * Fallback mapping used when no component mapping is configured. Skips
     * non-scalar values that we have no way of representing reliably.
     *
     * @param array $data Flat form data.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fallbackComponents(array $data): array
    {
        $components = [];
        foreach ($data as $key => $value) {
            if (!is_scalar($value) || $value === '') {
                continue;
            }
            $components[] = [
                'caption' => (string) $key,
                'values'  => [[
                    'type'  => 'STRING',
                    'value' => (string) $value,
                ]],
            ];
        }
        return $components;
    }

    /**
     * Perform an authenticated request against the Flexite API.
     *
     * @param string     $method  HTTP method (GET, POST, PATCH).
     * @param string     $url     Fully qualified endpoint URL.
     * @param object     $config  Flexite handler config.
     * @param array|null $body    Optional request body; JSON-encoded automatically.
     *
     * @return array|WP_Error Decoded JSON body on success, `WP_Error` on failure.
     */
    private function request(string $method, string $url, object $config, ?array $body = null): array|WP_Error
    {
        $args = [
            'method'  => $method,
            'timeout' => (int) ($config->timeout ?? self::DEFAULT_TIMEOUT),
            'headers' => $this->buildAuthHeaders($config, $body !== null),
        ];

        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $response = $this->wpService->wpRemoteRequest($url, $args);

        if ($this->wpService->isWpError($response)) {
            return new WP_Error(
                'flexite_request_failed',
                $this->wpService->__('Failed to communicate with Flexite.', 'modularity-frontend-form'),
                $response
            );
        }

        $statusCode = (int) $this->wpService->wpRemoteRetrieveResponseCode($response);
        $rawBody    = (string) $this->wpService->wpRemoteRetrieveBody($response);
        $decoded    = $rawBody === '' ? [] : json_decode($rawBody, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        if ($statusCode >= 200 && $statusCode < 300) {
            return $decoded;
        }

        return $this->buildErrorFromResponse($statusCode, $decoded);
    }

    /**
     * Build the Flexite authentication headers from the configuration.
     *
     * @return array<string, string>
     */
    private function buildAuthHeaders(object $config, bool $withJsonBody): array
    {
        $headers = [
            'Accept'          => 'application/json',
            'Accept-Language' => (string) ($config->acceptLanguage ?? self::DEFAULT_ACCEPT_LANGUAGE),
            'SECRET_KEY'      => (string) $config->secretKey,
            'LOGIN_TYPE'      => (string) $config->loginType,
            'USERNAME'        => (string) $config->username,
            'PASSWORD'        => (string) $config->password,
        ];
        if ($withJsonBody) {
            $headers['Content-Type'] = 'application/json';
        }
        return $headers;
    }

    /**
     * Build the `/process/{processId}/instance` URL.
     */
    private function buildInstancesUrl(object $config): string
    {
        return rtrim($config->baseUrl, '/')
            . '/process/' . urlencode((string) $config->processId) . '/instance';
    }

    /**
     * Build the `/process/{processId}/instance/{instanceId}` URL.
     */
    private function buildInstanceUrl(object $config, int $instanceId): string
    {
        return $this->buildInstancesUrl($config) . '/' . urlencode((string) $instanceId);
    }

    /**
     * Convert a Flexite error response into a `WP_Error` carrying the best
     * available message (including aggregated `componentErrors` for 422).
     *
     * @param int                  $statusCode HTTP status code.
     * @param array<string, mixed> $decoded    Decoded JSON body.
     */
    private function buildErrorFromResponse(int $statusCode, array $decoded): WP_Error
    {
        $message = isset($decoded['message']) && is_string($decoded['message'])
            ? $decoded['message']
            : $this->wpService->__('Flexite request failed.', 'modularity-frontend-form');

        if (!empty($decoded['componentErrors']) && is_array($decoded['componentErrors'])) {
            $parts = [];
            foreach ($decoded['componentErrors'] as $componentError) {
                if (!is_array($componentError)) {
                    continue;
                }
                $segment = '';
                if (isset($componentError['id'])) {
                    $segment .= '#' . (int) $componentError['id'] . ': ';
                }
                if (isset($componentError['message']) && is_string($componentError['message'])) {
                    $segment .= $componentError['message'];
                }
                if ($segment !== '') {
                    $parts[] = $segment;
                }
            }
            if ($parts !== []) {
                $message .= ' (' . implode('; ', $parts) . ')';
            }
        }

        return new WP_Error('flexite_http_' . $statusCode, $message, [
            'status' => $statusCode,
            'body'   => $decoded,
        ]);
    }

    /**
     * Shortcut to set a translated `WP_Error` on the handler result.
     */
    private function setHandlerError(string $message): void
    {
        $this->handlerResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::HandlerError->value,
                $this->wpService->__($message, 'modularity-frontend-form')
            )
        );
    }

    /**
     * Determine whether the given array is a list (sequentially indexed from 0).
     *
     * Uses the native `array_is_list` when available and falls back to a manual
     * key check for older PHP runtimes.
     */
    private function isList(array $array): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($array);
        }
        $expected = 0;
        foreach ($array as $key => $_value) {
            if ($key !== $expected++) {
                return false;
            }
        }
        return true;
    }
}
