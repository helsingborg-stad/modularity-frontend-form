<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TrueFalseFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $this->field['choices'] = [
            0 => $this->wpService->__('No', 'modularity-frontend-form'),
            1 => $this->wpService->__('Yes', 'modularity-frontend-form'),
        ];

        $mapped = (new RadioFieldMapper($this->field, $this->wpService, $this->lang, $this->config))->map();

        return $mapped;
    }
}