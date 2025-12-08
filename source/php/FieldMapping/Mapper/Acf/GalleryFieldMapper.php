<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class GalleryFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'gallery'))->map();

        $mapped['accept'] = !empty($this->field['mime_types'])
            ? str_replace(' ', ',', $this->field['mime_types'])
            : 'image/*';


        $mapped['filesMax'] = !empty($this->field['max']) ? $this->field['max'] : 50;
        $mapped['maxSize'] = !empty($this->field['max_size']) ? $this->field['max_size'] : 10;

        return $mapped ?? null;
    }
}