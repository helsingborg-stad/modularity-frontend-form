<?php

namespace ModularityFrontendForm\Api;

use WpService\WpService;
use Api\RestApiEndpointsRegistry;

class RestApiRoutes 
{
  public function __construct(private WpService $wpService, private $restApiEndpointsRegistry){}

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

    if($routes !== null) {
      $routes = array_map(function($route) {
        return $this->wpService->restUrl($route);
      }, $routes);

      $inlineAdded = $this->wpService->wpAddInlineScript(
        'modularity-frontend-form',
        'window.modularityFrontendFormRoutes = ' . json_encode($routes) . ';',
        'before'
      );

      if(!$inlineAdded) {
        throw new \Exception('Failed to add routes to inline script.');
      }
    }    
  }
}