<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\BasicFieldMapper;
class TextFieldMapper extends AbstractFieldMapper implements FieldMapperInterface
{
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