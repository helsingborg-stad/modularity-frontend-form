<?php

namespace ModularityFrontendForm\Api;

interface RestApiEndpointsRegistryInterface
{
    /**
     * Register a REST API endpoint
     *
     * @param RestApiEndpoint $endpoint
     * @throws \Exception
     * @return void
     */
    public static function add(RestApiEndpoint $endpoint);

    /**
     * Register all REST API endpoints
     *
     * @return void
     */
    public static function register();

    /**
     * Get all registered REST API routes
     *
     * @return array<RestApiEndpoint>|null
     */
    public static function getAddedEndpoints(): ?array;
}