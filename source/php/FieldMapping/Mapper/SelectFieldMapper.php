<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class SelectFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, 'select'))->map();

        $mapped['options']     = $this->field['choices'] ?: [];
        $mapped['preselected'] = $this->field['default_value'] ?? null;
        $mapped['placeholder'] = $this->field['placeholder']  ?? '';
        $mapped['multiple']    = $this->field['multiple'] ?? false;

        return $mapped;
    }
}