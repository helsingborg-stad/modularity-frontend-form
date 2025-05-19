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

    public function map(): ?array
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
            $mapped['attributeList']['data-js-required'] = 'true';
            unset($mapped['required']);
        }

        if (is_array($mapped)) {
            $mapped['fields'] = $subfields;
            $mapped['min']    = $this->field['min'] ?: 0;
            $mapped['max']    = $this->field['max'] ?: 100;
        }

        return $mapped ?? null;
    }
}