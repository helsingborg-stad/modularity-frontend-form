<?php

namespace ModularityFrontendForm\Config;

use WpService\WpService;
use ModularityFrontendForm\Config\ConfigInterface;

interface ConfigFactoryInterface
{
  /**
   * Creates a ModuleConfigInterface instance.
   *
   * @param int $moduleId
   * @return ConfigInterface
   */
  public static function create(WpService $wpService, ?string $filterPrefix): ConfigInterface;
}