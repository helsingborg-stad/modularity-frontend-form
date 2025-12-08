<?php

namespace ModularityFrontendForm\Module\FieldFormatterResolvers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\FieldMapping\Mapper;
use WpService\WpService;
use ModularityFrontendForm\Module\NamespaceHelper;

class AcfGroupFields implements FieldFormatterResolverInterface
{
    public function __construct(
        private AcfService $acfService,
        private ConfigInterface $config,
        private Mapper $mapper,
        private NamespaceHelper $namespaceHelper,
    ) {
        $this->mapper = $mapper;
    }

    /**
     * Format the given field data
     *
     * @param string $name The field data to format
     * @return array|null The formatted field data
     */
    public function resolve(string $name): array|null
    {
        $fields = $this->acfService->acfGetFields($name);

        if (!$this->canResolve($fields)) {
            return null;
        }

        $formattedGroup = [];
        foreach ($fields as $field) {
            $formattedGroup[] = $this->mapper->map($field);
        }

        return $this->namespaceHelper->namespaceFieldName(
            $formattedGroup
        );
    }

    /**
     * Check if the resolver can handle the given field name
     *
     * @param array $fields The field data to check
     * @return bool True if the resolver can handle the field data, false otherwise
     */
    private function canResolve(array $fields): bool
    {
        return !empty($fields);
    }
}
