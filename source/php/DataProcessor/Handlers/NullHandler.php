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

class NullHandler implements HandlerInterface {

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
   * Null handler that does nothing and returns an error.
   * This is used when no handler is found for the given data.
   */
  public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
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