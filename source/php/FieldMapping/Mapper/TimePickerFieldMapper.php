<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TimePickerFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'time'))->map();

        $mapped['placeholder']                         = ($this->field['placeholder'] ?? null) ?: null;
        $mapped['value']                               = ($this->field['default_value'] ?? null) ?: null;
        $mapped['minTime']                             = ($this->field['min_time'] ?? null) ?: null;
        $mapped['maxTime']                             = ($this->field['max_time'] ?? null) ?: null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }
}