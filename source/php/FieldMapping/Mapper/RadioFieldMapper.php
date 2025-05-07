<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class RadioFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'radio'))->map();

        if (is_array($mapped)) {
            $mapped['choices'] = [];
            $mapped['attributeList']['role'] = 'radiogroup';

            foreach ($this->field['choices'] as $key => $value) {
                $mapped['choices'][$key] = [
                    'type'     => $mapped['type'],
                    'label'    => $value,
                    'required' => $mapped['required'] ?? false,
                    'name'     => $this->field['key'],
                    'value'    => $key,
                    'checked'  => ($this->field['default_value'] ?? '') === $key,
                ];
            }
        }

        return $mapped ?? null;
    }
}