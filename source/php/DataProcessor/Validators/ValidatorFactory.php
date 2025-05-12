<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

class ValidatorFactory {
  public function createValidators(): array {
      return [
          new EmailValidator(),
          new RequiredFieldValidator(),
      ];
  }
}