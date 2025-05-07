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
        $id = 'row_repeater_id_' . $this->field['key'];

        foreach ($this->field['sub_fields'] as $index => $subfield) {
          $mappedSubfield = (new Mapper($subfield))->map();
          
          if(!is_null($mappedSubfield)) {
              $mappedSubfield['id']   = $id . '_' . $index;
              $mappedSubfield['name'] = $mappedSubfield['name'] . '[]';
              $mappedSubfield['id']   = $id . '_' . $index;

              $subfields[] = $mappedSubfield;
          }
        }

        $mapped = (new BasicFieldMapper($this->field, 'repeater'))->map();

        if (is_array($mapped)) {
            $mapped['fields'] = $subfields;
            $mapped['min']    = $this->field['min'] ?? 0;
            $mapped['max']    = $this->field['max'] ?? 100;
        }

        return $mapped ?? null;
    }
}