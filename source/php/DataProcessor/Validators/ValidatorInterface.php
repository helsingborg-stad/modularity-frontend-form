<?php

namespace ModularityFrontendForm\Handlers;

use ModularityFrontendForm\Validators\Result\ValidationResultInterface;

interface ValidatorInterface {
  public function validate(array $data): ?ValidationResultInterface;
}