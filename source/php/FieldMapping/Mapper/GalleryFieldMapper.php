<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class GalleryFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'gallery'))->map();

        if (is_array($mapped)) {
            $mapped['accept'] = !empty($this->field['mime_types'])
            ? str_replace(' ', ',', $this->field['mime_types'])
            : 'image/*';
            
            $mapped['filesMax'] = !empty($this->fields['max']) ? $this->fields['max'] : 100;
            $mapped['maxSize'] = !empty($this->fields['max_size']) ? $this->fields['max_size'] : 10;
        }

        return $mapped ?? null;
    }
}