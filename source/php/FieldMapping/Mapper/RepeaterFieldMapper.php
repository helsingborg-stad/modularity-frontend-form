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

    public function map(): array
    {
        $subfields = [];

        foreach ($this->field['sub_fields'] as $index => $subfield) {
            $subfield['key'] = $this->field['key'] . '_INDEX_REPLACE_' . $subfield['key'];
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

        return $mapped;
    }
}