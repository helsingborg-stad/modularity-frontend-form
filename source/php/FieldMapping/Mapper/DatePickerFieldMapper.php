<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class DatePickerFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, 'date'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?? null;
        $mapped['value']                               = $this->field['default_value'] ?? null;
        $mapped['minDate']                             = $this->field['min_date'] ?? null;
        $mapped['maxDate']                             = $this->field['max_date'] ?? null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }
}