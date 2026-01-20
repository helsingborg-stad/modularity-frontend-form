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

    // Parse URL to get host
    $parsed = parse_url($url);
    if (!$parsed || empty($parsed['host'])) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Invalid callback URL.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    // Resolve hostname to IP address
    $ip = gethostbyname($parsed['host']);
    
    // Block private IP ranges, localhost, and reserved IPs (SSRF protection)
    if ($this->isPrivateOrReservedIp($ip)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Callback URL cannot point to private or reserved IP addresses.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    // Apply WordPress filter for additional validation
    $isExternal = $this->wpService->applyFilters('http_request_host_is_external', true, $parsed['host'], $url);
    if (!$isExternal) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value, 
          $this->wpService->__('Callback URL blocked by security policy.', 'modularity-frontend-form')
        )
      );
      return false;
    }

    return true;
  }

  /**
   * Check if an IP address is private or reserved (SSRF protection)
   *
   * @param string $ip The IP address to check
   * @return bool True if the IP is private or reserved, false otherwise
   */
  private function isPrivateOrReservedIp(string $ip): bool
  {
    // Use PHP's built-in filter to check for private and reserved IP ranges
    // FILTER_FLAG_NO_PRIV_RANGE: Blocks private IP ranges (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16)
    // FILTER_FLAG_NO_RES_RANGE: Blocks reserved IP ranges (including 127.0.0.0/8, 169.254.0.0/16, etc.)
    return !filter_var(
      $ip,
      FILTER_VALIDATE_IP,
      FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
    );
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