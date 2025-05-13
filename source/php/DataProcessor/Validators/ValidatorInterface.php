<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;

interface ValidatorInterface {

  /**
   * Validate the given data.
   *
   * @param array $data The data to validate.
   * @return ValidationResultInterface|null The validation result or null if no errors.
   */
  public function validate(array $data): ?ValidationResultInterface;
}