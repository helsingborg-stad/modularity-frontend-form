<?php 

namespace ModularityFrontendForm\Config;

/**
 * A config implementation that returns null/empty values.
 * 
 * Useful for testing or scenarios where no configuration is needed.
 */
class NullConfig implements ConfigInterface
{
  public function __construct(
  ){}

  public function getModuleSlug(): string
  {
    return '';
  }

  public function getUnprintableKeys(): array
  {
    return [];
  }

  public function getFieldNamespace(null|string $fieldName = null): string
  {
    return '';
  }

  public function getMetaDataNamespace(?string $key = null): string
  {
    return '';
  }

  public function getKeysToBypass(): array
  {
    return [];
  }

  public function getAllowedHtmlTags(): array
  {
    return [];
  }

  public function getFilterPrefix(): string
  {
    return '';
  }

  public function createFilterKey(string $filter = ""): string
  {
    return '';
  }
}