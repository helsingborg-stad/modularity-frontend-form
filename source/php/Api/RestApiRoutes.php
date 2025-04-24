<?php

namespace ModularityFrontendForm\Api;

use WpService\WpService;
use Api\RestApiEndpointsRegistry;

class RestApiRoutes 
{
  public function __construct(private WpService $wpSevice, private $restApiEndpointsRegistry){}

  /**
   * Add hooks to WordPress
   * @return void
   */
  public function addHooks() {
    $this->wpService->addAction('wp_head', array($this, 'renderApiRoutes'));
  }

  /**
   * Render the API routes
   * @return void
   */
  public function renderApiRoutes() {
    $routes = $this->restApiEndpointsRegistry::getRegisteredRoutes();
    if($routes) {
      $routes = array_map(function($route) {
        return $this->wpSevice->restUrl($route);
      }, $routes);

      $this->wpService->wpLocalizeScript(
          'modularity-frontend-form',
          'modularityFrontendFormRoutes',
          $routes
      );
    }    
  }
}