<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class CheckboxFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'checkbox'))->map();

        if (is_array($mapped)) {
            $mapped['choices'] = [];

            foreach ($this->field['choices'] as $key => $value) {
                $mapped['choices'][$key] = [
                    'type'     => $mapped['type'],
                    'label'    => $value,
                    'required' => ($mapped['required'] ?? false) ?: false,
                    'name'     => $this->field['key'],
                    'value'    => $key,
                    'checked'  => in_array($key, $this->field['default_value'] ?? [], true),
                ];
            }
        }

        return $mapped ?? null;
    }
}