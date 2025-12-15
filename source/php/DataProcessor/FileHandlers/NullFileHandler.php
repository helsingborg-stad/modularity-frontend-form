<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
use WP_Error;

class NullFileHandler implements FileHandlerInterface {

    public function __construct(
      private Config $config, 
      private ModuleConfigInterface $moduleConfig,
      private WpService $wpService
    )
    {}

    public function handle(WP_REST_Request $request, ?int $postId = null): WP_Error|array {
        return new WP_Error(
          'no_file_handler', 
          $this->wpService->__('No file handler available.', 'modularity-frontend-form')
        );
    }
}