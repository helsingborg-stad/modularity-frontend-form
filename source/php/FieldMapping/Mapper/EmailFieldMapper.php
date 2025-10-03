<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class EmailFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, 'email'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?: '';
        $mapped['value']                               = $this->field['default_value'] ?: '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }
}