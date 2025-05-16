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
use WP_Error;

class NullHandler implements HandlerInterface {

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
        RestApiResponseStatusEnums::HandlerError->value, 
        $this->wpService->__('No handler found.', 'modularity-frontend-form')
      )
    );
    return $this->handlerResult;
  }
}