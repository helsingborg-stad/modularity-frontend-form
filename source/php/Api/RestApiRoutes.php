<?php

namespace ModularityFrontendForm\Api;

use WpService\WpService;

class RestApiRoutes implements \Modularity\HooksRegistrar\Hookable
{
  public function __construct(
    private WpService $wpService, 
    private  $restApiEndpointsRegistry
  ){}

  /**
   * Add hooks to WordPress
   * @return void
   */
  public function addHooks(): void {
    $this->wpService->addAction('init', array($this, 'renderApiRoutes'));
  }

  /**
   * Register the API routes
   * @return array|null
   */
  public function createApiRouteUrls(): ?array{
    $endpoints = $this->restApiEndpointsRegistry::getAddedEndpoints();

    if($endpoints !== null) {
      $endpoints = array_map(function($endpoint) {
        return $this->wpService->restUrl($endpoint->getRoute());
      }, $endpoints);

      return $endpoints;
    }

    return null;
  }

  /**
   * Render the API routes
   * 
   * @throws \Exception
   * @return void
   */
  public function renderApiRoutes() {
    $endpoints = $this->createApiRouteUrls();

    if($endpoints === null) {
      throw new \Exception('No API routes found.');
    }

    $this->wpService->addFilter(
      'Modularity/Module/FrontendForm/Assets/Data',
      function($data) use ($endpoints) {
        if(!is_array($data)) {
          $data = [];
        }
        $data['apiRoutes'] = $endpoints;
        return $data;
      }
    );
  }
}