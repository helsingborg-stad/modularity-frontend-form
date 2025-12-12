<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
interface FileHandlerInterface {
    public function __construct(
      Config $config, 
      ModuleConfigInterface $moduleConfig,
      WpService $wpService
    );

    public function handle(WP_REST_Request $request);
}