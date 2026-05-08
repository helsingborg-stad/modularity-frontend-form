<?php 

namespace ModularityFrontendForm\Config;

use PHPUnit\Framework\TestCase;

/**
 * A config implementation that returns null/empty values.
 * 
 * Useful for testing or scenarios where no configuration is needed.
 */
class NullConfigTest extends TestCase {
  public function testGetModuleSlugReturnsEmptyString(): void
  {
    $config = new NullConfig();
    static::assertSame('', $config->getModuleSlug());
  }

  public function testGetUnprintableKeysReturnsEmptyArray(): void
  {
    $config = new NullConfig();
    static::assertSame([], $config->getUnprintableKeys());
  }

  public function testGetFieldNamespaceReturnsEmptyString(): void
  {
    $config = new NullConfig();
    static::assertSame('', $config->getFieldNamespace());
    static::assertSame('', $config->getFieldNamespace('someField'));
  }

  public function testGetMetaDataNamespaceReturnsEmptyString(): void
  {
    $config = new NullConfig();
    static::assertSame('', $config->getMetaDataNamespace());
    static::assertSame('', $config->getMetaDataNamespace('someKey'));
  }

  public function testGetKeysToBypassReturnsEmptyArray(): void
  {
    $config = new NullConfig();
    static::assertSame([], $config->getKeysToBypass());
  }

  public function testGetAllowedHtmlTagsReturnsEmptyArray(): void
  {
    $config = new NullConfig();
    static::assertSame([], $config->getAllowedHtmlTags());
  }

  public function testGetFilterPrefixReturnsEmptyString(): void
  {
    $config = new NullConfig();
    static::assertSame('', $config->getFilterPrefix());
  }

  public function testCreateFilterKeyReturnsEmptyString(): void
  {
    $config = new NullConfig();
    static::assertSame('', $config->createFilterKey());
    static::assertSame('', $config->createFilterKey('someFilter'));
  }
}