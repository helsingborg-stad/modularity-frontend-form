<?php

namespace ModularityFrontendForm\DataProcessor\Validators\Result;

use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_Error;

class ValidationResult implements ValidationResultInterface
{
  private array $errors = [];

  /**
   * @inheritDoc
   */
  public function getIsValid(): bool
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