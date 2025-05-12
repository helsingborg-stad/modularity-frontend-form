<?php

namespace ModularityFrontendForm\Handlers;

interface ValidatorInterface {
  public function validate(array $data): bool;
}