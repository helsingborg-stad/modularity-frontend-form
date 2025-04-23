<?php

namespace ModularityFrontendForm\Api;

class RestApiEndpointsRegistry
{
    public static function add(RestApiEndpoint $endpoint)
    {
        $endpoint->register();
    }
}