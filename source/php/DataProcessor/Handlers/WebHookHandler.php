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
use ModularityFrontendForm\Hydratable\JsonDotHydrator;
use WP_Error;
use WP_REST_Request;

class WebHookHandler implements HandlerInterface {

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
    if($this->fileHandler === null) {
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

    if($this->validateCallbackUrl($config->callbackUrl) === false) {
      return $this->handlerResult;
    }

    if($this->trySendRequest($config->callbackUrl, $this->createBody($data, $config), $this->createHeaders($data, $config))) {
      return $this->handlerResult;
    }

    return $this->handlerResult;
  }

  /**
   * Validate the callback URL
   *
   * @param string $url The URL to validate
   * @return bool True if the URL is valid, false otherwise
   */
  private function validateCallbackUrl(string $url): bool
  {
    // Validate the URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Invalid callback url format.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    // Check if the URL is reachable
    $headers = @get_headers($url);
    if ($headers === false) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Callback url is not reachable.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    return true;
  }

  /**
   * Send the request to the webhook URL
   *
   * @param string $url The URL to send the request to
   * @param array $data The data to send in the request
   * @return bool True if the request was sent successfully, false otherwise
   */
  private function trySendRequest(string $url, array $data, array $headers = []): bool
  {
    error_log(print_r($headers, true));
    $response = $this->wpService->wpRemotePost($url, [
      'body' => \json_encode($data),
      'timeout' => 20,
      'headers' => $headers
    ]);

    if($this->wpService->isWpError($response)) {
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

    private function createBody(array $data, object $config): array {
    $formData = $this->parseFormData($data['mod-frontend-form']);
    return empty($config->body) 
      ? $formData 
      : \json_decode(
        (new JsonDotHydrator())->hydrate($config->body, $formData), 
        true
      );
  }

  private function parseFormData(array $formData): array {
    $dataAsJson = \json_encode($formData);
    preg_match_all('/field_[a-zA-Z0-9_]+/', $dataAsJson, $matches);
    $replaceIdWithNames = array_map(
      fn($v) => $this->acfService->getFieldObject($v)['name'], 
      array_combine($matches[0], $matches[0])
    );
    
    $dataAfterReplace = \json_decode(
      strtr($dataAsJson, $replaceIdWithNames), 
      true
    );

    return $dataAfterReplace;
  }
  
  private function createHeaders(array $data, object $config): array {
    return array_merge(
      [
        'Content-Type' => 'application/json',
      ],
      array_column($config->headers ?? [], 'value', 'header')
    );
  }
}