<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;

interface ValidatorInterface {
  public function validate(array $data): ?ValidationResultInterface;
}