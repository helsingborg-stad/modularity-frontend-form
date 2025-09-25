<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class ImageFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'image'))->map();

        if (is_array($mapped)) {
            $mapped['accept'] = !empty($this->field['mime_types'])
                ? str_replace(' ', ',', $this->field['mime_types'])
                : 'image/*';

            $mapped['maxSize'] = $this->fields['max_size'] ?? null;
        }

        return $mapped ?? null;
    }
}