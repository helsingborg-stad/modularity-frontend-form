<?php

namespace ModularityFrontendForm\Config;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class ConfigTest extends TestCase
{
    private function createConfig(): Config
    {
        $wpService = new FakeWpService([
            'applyFilters' => fn(string $hookName, mixed $value) => $value,
        ]);

        return new Config($wpService, 'modularity/frontend-form');
    }

    /**
     * @testdox getFieldNamespace() returns base namespace when no field name is provided
     */
    public function testGetFieldNamespaceWithoutFieldNameReturnsNamespace(): void
    {
        $config = $this->createConfig();

        static::assertSame('mod-frontend-form', $config->getFieldNamespace());
    }

    /**
     * @testdox getFieldNamespace() wraps regular field names in namespace brackets
     */
    public function testGetFieldNamespaceWithRegularFieldNameWrapsInBrackets(): void
    {
        $config = $this->createConfig();

        static::assertSame('mod-frontend-form[field_abc123]', $config->getFieldNamespace('field_abc123'));
    }

    /**
     * @testdox getFieldNamespace() does not double-wrap bracket-prefixed field names
     */
    public function testGetFieldNamespaceWithBracketPrefixedFieldNameDoesNotDoubleWrap(): void
    {
        $config = $this->createConfig();

        static::assertSame(
            'mod-frontend-form[field_parent][0][field_child]',
            $config->getFieldNamespace('[field_parent][0][field_child]')
        );
    }
}
