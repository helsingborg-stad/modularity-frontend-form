<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldFormatting\DisplayValue;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TextFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, $this->field['type']))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?? '';
        $mapped['value']                               = (new DisplayValue($this->wpService))->format($this->field['default_value'] ?? '', $this->field);
        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }
}