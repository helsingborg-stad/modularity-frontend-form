<?php
namespace ModularityFrontendForm\Config;

use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;

class ModuleConfigFactory implements ModuleConfigFactoryInterface
{
  public function __construct(
    private WpService $wpService,
    private AcfService $acfService,
    private ConfigInterface $config
  ) {}

  public function create(int $moduleId): ModuleConfigInterface
  {
    return new ModuleConfig(
      $this->wpService, 
      $this->acfService,
      $this->config, 
      $moduleId
    );
  }
}