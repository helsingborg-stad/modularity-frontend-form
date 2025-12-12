<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP;
use WP_REST_Request;
use WpService\WpService;

class NullFileHandler implements FileHandlerInterface {

    public function __construct(
      private Config $config, 
      private ModuleConfigInterface $moduleConfig,
      private WpService $wpService
    )
    {}

    public function handle(WP_REST_Request $request) {
        // Do nothing
    }
}