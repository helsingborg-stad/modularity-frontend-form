<?php

namespace ModularityFrontendForm\Module;

use ModularityFrontendForm\Config\ConfigInterface;

class NamespaceHelper
{
    public function __construct(
        private ConfigInterface $config,
    ) {}

    /**
     * Namespaces the field array to group form under a module namespace.
     * This function handles nested fields as well in a recursive manner.
     *
     * @param array $fields The fields to namespace.
     * 
     * @return array The namespaced fields.
     */
    public function namespaceFieldName(array $fields): array
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
    public function namespaceFieldNameString(string $name): string
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

}
