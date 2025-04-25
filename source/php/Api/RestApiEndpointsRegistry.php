<?php

namespace ModularityFrontendForm\Api;
use ModularityFrontendForm\Api\RestApiEndpoint;

class RestApiEndpointsRegistry implements RestApiEndpointsRegistryInterface
{
    private static array $endpoints = [];

    /**
     * @inheritDoc
     */
    public static function add(RestApiEndpoint $endpoint)
    {
      $route = $endpoint->getRoute();
      if (array_key_exists($route, self::$endpoints)) {
        throw new \Exception("Endpoint $route is already registered.");
      }
      self::$endpoints[$endpoint->getRouteKey()] = $endpoint;
    }

    /**
     * @inheritDoc
     */
    public static function register()
    {
      if(empty(self::$endpoints)) {
        throw new \Exception("No endpoints registered.");
      }
      foreach (self::$endpoints as $endpoint) {
        if(!($endpoint instanceof RestApiEndpoint)) {
          throw new \Exception("Invalid endpoint type.");
        }
        $endpoint->register();
      }
    }

    /**
     * @inheritDoc
     */
    public static function getAddedEndpoints(): ?array
    {
      return self::$endpoints ?? null;
    }
}