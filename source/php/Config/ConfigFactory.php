<?php
namespace ModularityFrontendForm\Config;

use WpService\WpService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\Config;

class ConfigFactory implements ConfigFactoryInterface
{
  public static function create(WpService $wpService, ?string $filterPrefix = null): ConfigInterface
  {
    return new Config(
      $wpService,
      $filterPrefix ??= 'Modularity/FrontendForm'
    );
  }
}