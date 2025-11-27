<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\WpService;

use ModularityFrontendForm\FieldMapping\Mapper;
use ModularityFrontendForm\Config\ConfigInterface;

class FormatSteps {

    public function __construct(
        private WpService $wpService, 
        private AcfService $acfService,
        private ConfigInterface $config,
        private object $lang
    ){}

    /**
     * Formats the steps to be used in the frontend.
     *
     * @param array $steps The steps to format.
     * 
     * @return array The formatted steps.
     */
    public function formatSteps(array $steps) 
    {
        $formattedSteps = [];
        foreach ($steps as $key => $step) {
            $formattedSteps[$key]['title'] = $step['formStepTitle'] ?? null;
            $formattedSteps[$key]['description'] = $step['formStepContent'] ?? null;
            $formattedSteps[$key]['fields'] = $this->formatStep($step);
        }

        return $formattedSteps;
    }

    /**
     * Formats a single step to be used in the frontend.
     *
     * @param array $unformattedStep The unformatted step to format.
     * 
     * @return array The formatted step.
     */
    public function formatStep(array $unformattedStep) 
    {
        $fieldGroups = $unformattedStep['formStepGroup'] ?? [];

        $formattedStep = [];
        foreach ($fieldGroups as $fieldGroup) {
            $fields = $this->acfService->acfGetFields($fieldGroup);
            foreach ($fields as $field) {
                $formattedStep[] = (new Mapper($field, $this->wpService, $this->lang))->map();
            }

            $formattedStep = $this->namespaceFieldName($formattedStep);
        }

        return $formattedStep;
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
        $isArray    = str_ends_with($name, '[]');
        $baseName   = $isArray ? substr($name, 0, -2) : $name;
        return sprintf(
            '%s[%s]%s', 
            $this->config->getFieldNamespace(),
            $baseName, 
            $isArray ? '[]' : ''
        );
    }
}