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
use WP_Error;

class WebHookHandler implements HandlerInterface {

  use GetModuleConfigInstanceTrait;

  public function __construct(
      private WpService $wpService,
      private AcfService $acfService,
      private ConfigInterface $config,
      private ModuleConfigInterface $moduleConfigInstance,
      private object $params,
      private HandlerResultInterface $handlerResult = new HandlerResult(),
      private FileHandlerInterface $fileHandler = new NullFileHandler()
  ) {
  }

  /**
   * Handle the data
   *
   * @param array $data The data to handle
   * @return HandlerResultInterface|null The result of the handling
   */
  public function handle(array $data): ?HandlerResultInterface
  {
    $config = $this->moduleConfigInstance->getWebHookHandlerConfig();

    if($this->validateCallbackUrl($config->callbackUrl) === false) {
      return $this->handlerResult;
    }

    if($this->trySendRequest($config->callbackUrl, $data)) {
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
  private function trySendRequest(string $url, array $data): bool
  {
    $response = $this->wpService->wpRemotePost($url, [
      'body' => $data,
      'timeout' => 20,
      'headers' => [
        'Content-Type' => 'application/json',
      ],
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
}