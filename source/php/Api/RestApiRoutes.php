<?php

namespace ModularityFrontendForm\Api;

use WpService\WpService;
use ModularityFrontendForm\Api\RestApiEndpointsRegistry;

class RestApiRoutes 
{
  public function __construct(private WpService $wpSevice, private $restApiEndpointsRegistry){}

  public function addHooks() {
    $this->wpService->addAction('wp_head', array($this, 'renderApiRoutes'));
  }

  public function renderApiRoutes() {
    $routes = $this->restApiEndpointsRegistry::getRegisteredRoutes();
    

    //Add wp localize object 
    $this->wpService->addAction('wp_localize_script', function($handle, $objectName, $data) use ($routes) {
      if ($handle === 'frontend-form') {
        $data['apiRoutes'] = $routes;
      }
      return $data;
    }, 10, 3);
  }
}