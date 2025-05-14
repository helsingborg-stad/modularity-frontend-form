<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;

interface HandlerInterface {
  public function handle(array $data): ?HandlerResultInterface;
}