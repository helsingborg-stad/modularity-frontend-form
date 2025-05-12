<?php

namespace ModularityFrontendForm\DataProcessor\Validators\Result;

use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use WP_Error;

class ValidationResult implements ValidationResultInterface
{
  private array $errors = [];

  /**
   * Check if the validation result is valid.
   *
   * @return bool True if valid, false otherwise.
   */
  public function getIsValid(): bool
  {
    return (bool) empty($this->errors);
  }

  /**
   * Get the validation errors.
   *
   * @return array An array of WP_Error objects.
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * Add an error to the validation result.
   *
   * @param WP_Error $error The error to add.
   */
  public function setError(WP_Error $error): void
  {
    $this->errors[] = $error;
  }
}