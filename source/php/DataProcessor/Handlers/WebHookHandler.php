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
use ModularityFrontendForm\Helper\JsonDotHydrator;
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
          $this->wpService->__('Failed to send data to webhook.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    return true;
  }

  private function createBody(array $data, object $config): array|null
  {
    $formData = $this->parseFormData($data['mod-frontend-form']);
    $formData['*'] = $formData;

    return empty($config->body)
      ? null
      : \json_decode(
        (new JsonDotHydrator())->hydrate($config->body, $formData),
        true
      );
  }

  private function createHeaders(array $data, object $config): array
  {
    return array_merge(
      [
        'Content-Type' => 'application/json',
      ],
      array_column($config->headers ?? [], 'value', 'header')
    );
  }

  private function parseFormData(array $formData): array
  {
    $unserializersByFieldType = [
      'true_false'  => fn($v) => (bool)(int) $v,
      'null'        => fn($v) => $v
    ];

    $fieldObjects = array_map(
      fn($fieldId) => $this->acfService->getFieldObject($fieldId),
      array_combine(array_keys($formData), array_keys($formData))
    );

    foreach ($fieldObjects as $fieldId => $fieldObject) {
      $unserializer = in_array($fieldObject['type'], array_keys($unserializersByFieldType))
        ? $fieldObject['type']
        : 'null';
      $unserializeFn = $unserializersByFieldType[$unserializer];
      $formData[$fieldId] = $unserializeFn($formData[$fieldId]);
    }

    $dataAsJson = \json_encode($formData);

    preg_match_all('/field_[a-zA-Z0-9_]+/', $dataAsJson, $matches);

    $replaceIdWithNames = array_map(
      fn($v) => $fieldObjects[$v]['name'] ?? $v,
      array_combine($matches[0], $matches[0])
    );

    return \json_decode(strtr($dataAsJson, $replaceIdWithNames), true);
  }
}
