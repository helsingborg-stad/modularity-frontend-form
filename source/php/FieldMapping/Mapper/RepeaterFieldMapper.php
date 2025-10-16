<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

use ModularityFrontendForm\FieldMapping\Mapper;

class RepeaterFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;
    private array $keyRewrites = [];
    private string $replaceKey = 'index_replace';

    public function map(): array
    {
        $subfields = [];

        foreach ($this->field['sub_fields'] as $subfield) {
            $this->keyRewrites[$subfield['key']] = $this->field['key'] . '_' . $this->replaceKey . '_' . $subfield['key'];
        }

        // Subfields in a repeater needs to have unique keys and ids so we rewrite them here
        foreach ($this->field['sub_fields'] as $index => $subfield) {
            $subfield['key'] = $this->keyRewrites[$subfield['key']];
            $subfield['conditional_logic'] =  $this->rewriteConditionalLogic($subfield['conditional_logic']) ?? null;
            $subfield['wrapper']['id']  = $subfield['key'] . '_' . $index;

            $mappedSubfield = (new Mapper($subfield, $this->wpService, $this->lang))->map();

            if(!is_null($mappedSubfield)) {
                $subfields[] = $mappedSubfield;
            }
        }

        $mapped = (new BasicFieldMapper($this->field, 'repeater'))->map();

        if ($mapped['required']) {
            $this->field['min'] = $this->field['min'] ?: 1;
            unset($mapped['required']);
        }

        $mapped['fields'] = $subfields;
        $mapped['min']    = $this->field['min'] ?: 0;
        $mapped['max']    = $this->field['max'] ?: 100;

        $mapped['attributeList']['data-js-min-rows'] = $mapped['min'];
        $mapped['attributeList']['data-js-max-rows'] = $mapped['max'];
        $mapped['buttonLabel'] = $this->field['button_label'] ?: $this->lang->newRow;

        $mapped['classList'][] = 'mod-frontend-form__repeater';

        return $mapped;
    }

    private function rewriteConditionalLogic($logic)
    {
        if (!is_array($logic)) {
            return $logic;
        }

        foreach ($logic as $groupIndex => $group) {
            foreach ($group as $ruleIndex => $rule) {
                if (isset($this->keyRewrites[$rule['field']])) {
                    $logic[$groupIndex][$ruleIndex]['field'] = $this->keyRewrites[$rule['field']];
                }
            }
        }

        return $logic;
    }
}