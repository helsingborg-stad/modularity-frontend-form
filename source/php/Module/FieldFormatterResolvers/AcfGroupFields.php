<?php

namespace ModularityFrontendForm\Module\FieldFormatterResolvers;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\FieldMapping\Mapper;
use WpService\WpService;

class AcfGroupFields implements FieldFormatterResolverInterface
{
    public function __construct(
        private AcfService $acfService,
        private ConfigInterface $config,
        private Mapper $mapper,
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

        $formattedGroup = $this->namespaceFieldName($formattedGroup);
        return $formattedGroup;
    }

    /**
     * Namespaces the field array to group form under a module namespace.
     * This function handles nested fields as well in a recursive manner.
     *
     * @param array $fields The fields to namespace.
     * 
     * @return array The namespaced fields.
     */
    private function namespaceFieldName(array $fields): array
    {
        foreach ($fields as $key => $field) {
            if (isset($field['name'])) {
                $fields[$key]['name'] = $this->namespaceFieldNameString($field['name']);
            }

            if (isset($field['fields']) && is_array($field['fields'])) {
                $fields[$key]['fields'] = $this->namespaceFieldName($field['fields']);
            }
        }

        return $fields;
    }

    /**
     * Namespaces the field name to group form under a module namespace.
     * This function handles single field names, and array field names.
     *
     * @param string $name The field name.
     * 
     * @return string The namespaced field name.
     */
    private function namespaceFieldNameString(string $name): string
    {
        //Assume that the base name is the name itself
        $baseName = $name;

        // Check if the field is an array (ends with [])
        $isArray = str_ends_with($name, '[]');
        if ($isArray) {
            $baseName = substr($name, 0, -2);
        }

        // Check if the field is a repeater (starts and ends with [ ])
        $isRepeater = str_starts_with($baseName, '[') && str_ends_with($baseName, ']');
        if ($isRepeater) {
            $baseName = trim($baseName, '[]');
        }

        return sprintf(
            '%s[%s]%s',
            $this->config->getFieldNamespace(),
            $baseName,
            $isArray ? '[]' : '' //Add back [] if it was an array
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
