<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;
class TextFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->field['type']))->map();

        if(is_array($mapped)) {
            $mapped['placeholder']                         = $this->field['placeholder'] ?? '';
            $mapped['value']                               = $this->field['default_value'] ?? '';
            $mapped['moveAttributesListToFieldAttributes'] = false;
        }

        return $mapped ?? null;
    }
}