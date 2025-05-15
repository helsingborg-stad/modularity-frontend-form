<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use WpService\WpService; 
use AcfService\AcfService; 
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use WP_Error;

class WebHookHandler implements HandlerInterface {

  use GetModuleConfigInstanceTrait;

  public function __construct(
      private WpService $wpService,
      private AcfService $acfService,
      private ConfigInterface $config,
      private ModuleConfigInterface $moduleConfigInstance,
      private HandlerResultInterface $handlerResult = new HandlerResult()
  ) {
  }

  /**
   * Null handler that does nothing and returns an error.
   * This is used when no handler is found for the given data.
   */
  public function handle(array $data): ?HandlerResultInterface
  {
    $this->handlerResult->setError(
      new WP_Error(
        "handler_error", 
        $this->wpService->__('The webhook handler is not done yet. Please check in again in next version.', 'modularity-frontend-form')
      )
    );
    return $this->handlerResult;
  }
}