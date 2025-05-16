<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Result;

use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_Error;

class HandlerResult implements HandlerResultInterface
{
  private array $errors = [];

  /**
   * @inheritDoc
   */
  public function isOk(): bool
  {
    return (bool) empty($this->errors);
  }

  /**
   * @inheritDoc
   */
  public function getErrors(): ?array
  {
    return $this->errors ?: null;
  }

  /**
   * @inheritDoc
   */
  public function setError(WP_Error $error): void
  {
    if(RestApiResponseStatusEnums::from($error->get_error_code())) {
      $this->errors[] = $error;
    }
  }
}