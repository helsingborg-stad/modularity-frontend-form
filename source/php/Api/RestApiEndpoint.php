<?php

namespace ModularityFrontendForm\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class RestApiEndpoint
{
    private const NAMESPACE = 'modularity-frontend-form/v1';
    private const ROUTE     = null;
    private const KEY       = null;

    final public function register()
    {
      $this->handleRegisterRestRoute();
    }

    final public function getRoute(): string
    {
      return self::NAMESPACE . (self::ROUTE ?? 'undefined');
    }

    final public function getRouteKey(): ?string
    {
      return self::KEY ?? null;
    }

    abstract public function handleRegisterRestRoute(): bool;

    /**
     * @return WP_REST_Response|WP_Error
     */
    abstract public function handleRequest(WP_REST_Request $request);
}