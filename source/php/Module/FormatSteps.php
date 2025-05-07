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
                //TODO: REPLACE WITH MAPPER
                //$formattedStep[] = (new Mapper($field))->map();

                $formattedStep[] = $this->fieldMapper($field);
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

    /**
     * Maps the field to a format that can be used in the frontend.
     *
     * @param array $field The field to map.
     * 
     * @return array The mapped field.
     * 
     * 
     * TODO: REMOVE THIS FUNCTION AND REPLACE WITH MAPPER
     */
    private function fieldMapper(array $field)
    {
        switch ($field['type']) {
            case 'text':
            case 'email':
            case 'url':
            case 'textarea':
            case 'true_false':
            case 'select':
            case 'checkbox':
            case 'message':
            case 'file':
            case 'number':
            case 'image':
            case 'radio':
            case 'repeater':
            case 'date_picker':
            case 'time_picker':
            case 'button_group':
            case 'google_map':
                return (new Mapper($field))->map();
            
            //TODO: Migrate these to mappers
            case 'taxonomy':
                return $this->mapTaxonomy($field);
        }

    }
    
    private function mapTaxonomy(array $field): array
    {
        $field['choices'] = $this->structureTerms($this->getTermsFromTaxonomy($field));
        return (new Mapper($field))->map();
    }

    private function structureTerms(array $terms): array
    {
        $structured = [];

        foreach ($terms as $term) {
            $structured[$term->term_id] = $term->name ?? $term->term_id;
        }

        return $structured;
    }

    private function getTermsFromTaxonomy(array $field): array
    {
        if (empty($field['taxonomy'])) {
            return [];
        }

        $terms = get_terms([
            'taxonomy'   => $field['taxonomy'],
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        return $terms;
    }
}