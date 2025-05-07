<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TextareaFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'textarea'))->map();

        if (is_array($mapped)) {
            $mapped['placeholder']        = $this->field['placeholder'] ?? '';
            $mapped['value']              = $this->field['default_value'] ?? '';
            $mapped['rows']               = $this->field['rows'] ?? 5;
            $mapped['fieldAttributeList'] = [
                'data-js-conditional-logic' => $this->structureConditionalLogic($this->field['conditional_logic'] ?? null),
            ];
        }

        return $mapped ?? null;
    }
}