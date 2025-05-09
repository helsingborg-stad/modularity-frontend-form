<?php
namespace ModularityFrontendForm\Config;

interface ModuleConfigFactoryInterface
{
  /**
   * Creates a ModuleConfigInterface instance.
   *
   * @param int $moduleId
   * @return ModuleConfigInterface
   */
  public function create(int $moduleId): ModuleConfigInterface;
}