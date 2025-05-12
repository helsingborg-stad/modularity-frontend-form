<?php

namespace ModularityFrontendForm\Validators;

class ValidatorFactory {
  public function createValidators(): array {
      return [
          new EmailValidator(),
          new RequiredFieldValidator(),
      ];
  }
}