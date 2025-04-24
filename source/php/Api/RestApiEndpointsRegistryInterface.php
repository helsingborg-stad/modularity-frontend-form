<?php

namespace ModularityFrontendForm\Api;

interface RestApiEndpointsRegistry
{
    public static function add(RestApiEndpoint $endpoint);
    public static function getRegisteredRoutes(): array;
}