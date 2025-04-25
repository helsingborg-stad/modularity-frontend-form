<?php

namespace ModularityFrontendForm\Api;

interface RestApiEndpointsRegistryInterface
{
    public static function add(RestApiEndpoint $endpoint);
    public static function getRegisteredRoutes(): ?array;
}