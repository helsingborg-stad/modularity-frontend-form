<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class NumberFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, 'number'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?: '';
        $mapped['value']                               = $this->field['default_value'] ?: '';
        $mapped['moveAttributesListToFieldAttributes'] = false;
        $mapped['attributeList']['min']                = $this->field['min'] ?? null;
        $mapped['attributeList']['max']                = $this->field['max'] ?? null;

        return $mapped;
    }
}