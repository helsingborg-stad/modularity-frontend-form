<?php

namespace ModularityFrontendForm\Module\FieldFormatterResolvers;

use ModularityFrontendForm\FieldMapping\Mapper;
use ModularityFrontendForm\Module\GroupHelper;
use ModularityFrontendForm\Module\NamespaceHelper;

class WordpressFields implements FieldFormatterResolverInterface
{
    private array $wordpressFields;

    public function __construct(
        private GroupHelper $groupHelper,
        private NamespaceHelper $namespaceHelper,
        private Mapper $mapper,
    ) {
        $this->wordpressFields = $this->groupHelper->getBasicWordpressFields();
        $formattedGroup = $this->namespaceHelper->namespaceFieldName(
            $this->wordpressFields
        );

        var_dump($formattedGroup);
    }

    /**
     * Format the given field data
     *
     * @param string $name The field data to format
     * @return array The formatted field data
     */
    public function resolve(string $name): array|null
    {
        if (!$this->canResolve($name)) {
            return null;
        }
        return [$this->mapper->map($name)];
    }

    /**
     * Check if the resolver can handle the given field name
     *
     * @param string $name The field name to check
     * @return bool True if the resolver can handle the field name, false otherwise
     */
    private function canResolve(string $name): bool
    {
        return array_key_exists($name, $this->wordpressFields);
    }
}