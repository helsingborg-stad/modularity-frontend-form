<?php

namespace ModularityFrontendForm\Api;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

abstract class RestApiEndpoint
{
    protected const NAMESPACE = 'modularity-frontend-form/v1';
    protected const ROUTE     = null;
    protected const KEY       = null;

    final public function register()
    {
      $this->handleRegisterRestRoute();
    }

    final public function getRoute(): string
    {
      return static::NAMESPACE . "/" . (static::ROUTE ?? 'undefined');
    }

    final public function getRouteKey(): ?string
    {
      return static::KEY ?? null;
    }

    abstract public function handleRegisterRestRoute(): bool;

    /**
     * @return WP_REST_Response|WP_Error
     */
    abstract public function handleRequest(WP_REST_Request $request);
}