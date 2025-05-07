<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\Contracts\__;

use ModularityFrontendForm\FieldMapping\Mapper;
class FormatSteps {
    public function __construct(private AcfService $acfService)
    {
    }

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
            $formattedSteps[$key]['title'] = !empty($step['formStepIncludesPostTitle']) ?
                ($step['formStepTitle'] ?? null) :
                null;

            $formattedSteps[$key]['description'] = $step['formStepIncludesPostTitle'] ?? null;
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
                $formattedStep[] = (new Mapper($field))->map();
            }

            $formattedStep = $this->namespaceFieldName($formattedStep);
        }

        return $formattedStep;
    }

    /**
     * Namespaces the field array to group form under a module namespace.
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
        if (substr($name, -2) === '[]') {
            $suffix = substr($name, 0, -2);
            $name   = str_replace('[]', '', $name);
        }
        return sprintf("mod-frontedform['%s']%s", $name, $suffix ?? '');
    }
}