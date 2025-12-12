<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use WP_REST_Request;

interface HandlerInterface {
  public function handle(array $data, WP_REST_Request $request): ?HandlerResultInterface;
}