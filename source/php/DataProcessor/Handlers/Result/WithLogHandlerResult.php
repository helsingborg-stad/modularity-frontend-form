<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Result;

use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use Psr\Log\LoggerInterface;
use WP_Error;

class WithLogHandlerResult implements HandlerResultInterface
{
  public function __construct(private HandlerResultInterface $handlerResult, private LoggerInterface $logger)
  {}

  /**
   * @inheritDoc
   */
  public function isOk(): bool
  {
    return $this->handlerResult->isOk();
  }

  /**
   * @inheritDoc
   */
  public function getErrors(): ?array
  {
    return $this->handlerResult->getErrors();
  }

  /**
   * @inheritDoc
   */
  public function setError(WP_Error $error): void
  {
    $this->handlerResult->setError($error);
    if ($this->isOk()) return;

    foreach ( $error->get_error_codes() as $code ) {
        foreach ( $error->get_error_messages( $code ) as $message ) {
            $this->logger->error("$message  ($code)");
            if ($error->get_error_data($code)) $this->logger->debug(\json_encode((array) $error->get_error_data($code), \JSON_PRETTY_PRINT));
        }
    }
  }
}