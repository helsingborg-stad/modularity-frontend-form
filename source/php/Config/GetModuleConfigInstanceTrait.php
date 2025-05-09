<?php

namespace ModularityFrontendForm\Config;

use ModularityFrontendForm\Config\ModuleConfigInterface;

trait GetModuleConfigInstanceTrait
{
  /**
   * Returns the module config instance
   *
   * @param int $moduleId The module ID
   *
   * @return ModuleConfigInterface The module config instance
   */
  public function getModuleConfigInstance(int $moduleId): ModuleConfigInterface
  {
    static $moduleConfigCache = [];
    if (!isset($moduleConfigCache[$moduleId])) {
      $moduleConfigCache[$moduleId] = $this->moduleConfigFactory->create($moduleId);
    }
    return $moduleConfigCache[$moduleId];
  }
}