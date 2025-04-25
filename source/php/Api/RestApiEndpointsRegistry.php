<?php

namespace ModularityFrontendForm\Api;
use ModularityFrontendForm\Api\RestApiEndpointsRegistryInterface;

class RestApiEndpointsRegistry implements RestApiEndpointsRegistryInterface
{
    private static array $endpoints = [];

    /**
     * Register a REST API endpoint
     *
     * @param RestApiEndpoint $endpoint
     * @throws \Exception
     * @return void
     */
    public static function add(RestApiEndpoint $endpoint)
    {
      //Get route and check if it is already registered
      $route = $endpoint->getRoute();

      if (in_array($route, self::$endpoints)) {
        throw new \Exception("Endpoint $route is already registered.");
      }

      // Register the endpoint
      $endpoint->register();

      // Add the endpoint to the registry
      self::$endpoints[
        $endpoint->getRouteKey()
      ] = $endpoint->getRoute();
    }

    /**
     * Get all registered REST API routes
     *
     * @return array
     */
    public static function getRegisteredRoutes(): ?array
    {
      return self::$endpoints ?? null;
    }
}