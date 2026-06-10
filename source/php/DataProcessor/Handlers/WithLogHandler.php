<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\DataProcessor\Handlers\HandlerInterface;
use Psr\Log\LoggerInterface;
use WP_REST_Request;

class WithLogHandler implements HandlerInterface {

  use GetModuleConfigInstanceTrait;

  public function __construct(
      private HandlerInterface $handler,
      private LoggerInterface $logger
  ) { }

  /**
   * Null handler that does nothing and returns an error.
   * This is used when no handler is found for the given data.
   */
  public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface
  {
    $this->logger->info('Processing data using handler instance from mod-frontend-from configuration (module-id: {module-id}, holding-post-id: {holding-post-id})', $data);
    return $this->handler->handle($data, $request);
  }
}