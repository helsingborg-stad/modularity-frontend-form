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
                return (new Mapper($field))->map();
            
            //TODO: Migrate these to mappers
            case 'taxonomy':
                return $this->mapTaxonomy($field);
            case 'google_map':
                return $this->mapGoogleMap($field);
        }

    }

    /**
     * Maps the field to a format that can be used in the frontend.
     *
     * @param array $field The field to map.
     * @param string $type The type of field to map.
     * 
     * @return array The mapped field.
     */
    private function mapBasic(array $field, string $type)
    {
        return [
            'type'        => $type,
            'view'        => $type,
            'label'       => $field['label'],
            'name'        => $field['key'],
            'required'    => $field['required'] ?? false,
            'description' => $field['instructions'] ?? '',
            'attributeList' => [
                'data-js-conditional-logic' => $this->structureConditionalLogic($field['conditional_logic'] ?? null),
                'data-js-field' => $type,
                'data-js-field-name' => $field['key'],
            ]
        ];
    }


    private function mapMessage(array $field): array
    {
        $mapped = $this->mapBasic($field, 'message');

        $mapped['message'] = $field['message'] ?? '';

        return $mapped;
    }

    private function mapGoogleMap(array $field): array
    {
        $mapped = $this->mapBasic($field, 'googleMap');

        $mapped['height'] = $field['height'] ?: '400';
        $mapped['lat'] = $field['center_lat'] ?: '59.32932';
        $mapped['lng'] = $field['center_lng'] ?: '18.06858';
        $mapped['zoom'] = $field['zoom'] ?: '14';
        $mapped['attributeList']['style'] = 'height: ' . $mapped['height'] . 'px; position: relative;';

        return $mapped;
    }

    private function mapImage(array $field): array
    {
        // TODO: imageinput component missing description
        $mapped = $this->mapBasic($field, 'image');

        $mapped['accept'] = $field['mime_types'] ? str_replace(' ', ',', $field['mime_types']) : 'image/*';

        return $mapped;
    }
    
    private function mapFile(array $field): array
    {
        // TODO: What should default values be for fileinput?
        $mapped = $this->mapBasic($field, 'file');

        $mapped['accept'] = $field['mime_types'] ? str_replace(' ', ',', $field['mime_types']) : 'audio/*,video/*,image/*';

        return $mapped;
    }

    private function mapNumber(array $field): array
    {
        // TODO: Append ex. SEK?
        $mapped = $this->mapBasic($field, 'number');

        $mapped['placeholder']                         = $field['placeholder'] ?? '';
        $mapped['value']                               = $field['default_value'] ?? '';
        $mapped['moveAttributesListToFieldAttributes'] = false;
        $mapped['attributeList']['min']                = $field['min'] ?? null;
        $mapped['attributeList']['max']                = $field['max'] ?? null;

        return $mapped;
    }

    private function mapTextarea(array $field)
    {
        // TODO: Max words (maxlength)?
        $mapped = $this->mapBasic($field, 'textarea');

        $mapped['placeholder']        = $field['placeholder'] ?? '';
        $mapped['value']              = $field['default_value'] ?? '';
        $mapped['rows']               = $field['rows'] ?? 5;
        $mapped['fieldAttributeList'] = ['data-js-conditional-logic' => $this->structureConditionalLogic($field['conditional_logic'] ?? null)];

        return $mapped;
    }

    private function mapUrl(array $field): array
    {
        $mapped = $this->mapBasic($field, 'url');

        $mapped['placeholder']                         = $field['placeholder'] ?? '';
        $mapped['value']                               = $field['default_value'] ?? '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapButtonGroup(array $field): array
    {
        return $this->mapRadio($field);
    }

    private function mapTimePicker(array $field): array
    {
        $mapped = $this->mapBasic($field, 'time');

        $mapped['placeholder']                         = $field['placeholder'] ?? null;
        $mapped['value']                               = $field['default_value'] ?? null;
        $mapped['minTime']                             = $field['min_time'] ?? null;
        $mapped['maxTime']                             = $field['max_time'] ?? null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapDatePicker(array $field): array
    {
        $mapped = $this->mapBasic($field, 'date');
        // TODO: Do we need to set format?
        $mapped['placeholder']                         = $field['placeholder'] ?? null;
        $mapped['value']                               = $field['default_value'] ?? null;
        $mapped['minDate']                             = $field['min_date'] ?? null;
        $mapped['maxDate']                             = $field['max_date'] ?? null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapRepeater(array $field): array
    {
        $subfields = [];
        $id = 'row_repeater_id_' . $field['key'];
        foreach ($field['sub_fields'] as $index => $subfield) {
            $mappedSubfield = $this->fieldMapper($subfield);
            $mappedSubfield['id'] = $id . '_' . $index;
            $mappedSubfield['name'] = $mappedSubfield['name'] . '[]';
            $subfields[] = $mappedSubfield;
        }

        $mapped = $this->mapBasic($field, 'repeater');

        $mapped['fields'] = $subfields;
        $mapped['min']    = $field['min'] ?? 0;
        $mapped['max']    = $field['max'] ?? 100;

        return $mapped;
    }
    
    private function mapTaxonomy(array $field): array
    {
        $field['choices'] = $this->structureTerms($this->getTermsFromTaxonomy($field));

        $type = $field['field_type'] ?? 'checkbox';

        switch ($type) {
            case 'radio':
                return $this->mapRadio($field);
            case 'select':
                return $this->mapSelect($field);
            case 'multi_select':
                $field['multiple'] = true;
                return $this->mapSelect($field);
            case 'checkbox':
            default:
                return $this->mapCheckbox($field);
        }
    }

    private function mapText(array $field): array
    {
        $mapped = $this->mapBasic($field, 'text');
        
        // TODO: Add maxLength to component (field)?
        $mapped['placeholder']                         = $field['placeholder'] ?? '';
        $mapped['value']                               = $field['default_value'] ?? '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapEmail(array $field): array
    {
        $mapped = $this->mapBasic($field, 'email');

        $mapped['placeholder']                         = $field['placeholder'] ?? '';
        $mapped['value']                               = $field['default_value'] ?? '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }

    private function mapTrueFalse(array $field): array
    {

        $field['choices'] = [
            0 => __('No', 'modularity-frontend-form'),
            1 => __('Yes', 'modularity-frontend-form'),
        ];

        $mapped = $this->mapRadio($field);

        $mapped['attributeList']['style'] = 'display: flex;';

        return $mapped;
    }

    private function mapCheckbox(array $field): array
    {
        $mapped = $this->mapBasic($field, 'checkbox');

        $mapped['choices'] = [];
        foreach ($field['choices'] as $key => $value) {
            $mapped['choices'][$key] = [
                'type' => $mapped['type'],
                'label' => $value,
                'required' => $mapped['required'] ?? false,
                'name' => $field['key'],
                'value' => $key,
                'checked' => in_array($key, ($field['default_value'] ?? [])),
            ];
        }

        return $mapped;
    }

    private function mapRadio(array $field): array
    {
        $mapped = $this->mapBasic($field, 'radio');
        $mapped['choices'] = [];
        $mapped['attributeList']['role'] = 'radiogroup';
        foreach ($field['choices'] as $key => $value) {
            $mapped['choices'][$key] = [
                'type' => $mapped['type'],
                'label' => $value,
                'required' => $mapped['required'] ?? false,
                'name' => $field['key'],
                'value' => $key,
                'checked' => ($field['default_value'] ?? '') === $key,
            ];
        }

        return $mapped;
    }

    private function mapSelect(array $field): array
    {
        $mapped = $this->mapBasic($field, 'select');

        $mapped['options']     = $field['choices'] ?? [];
        $mapped['preselected'] = $field['default_value'] ?? null;
        $mapped['placeholder'] = $field['placeholder'] ?? '';
        $mapped['multiple']    = $field['multiple'] ?? false;

        return $mapped;
    }

    private function structureConditionalLogic($conditionalLogic)
    {
        if (!is_array($conditionalLogic)) {
            return $conditionalLogic;
        }

        return json_encode($conditionalLogic);
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