<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Wordpress;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\BasicFieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\BasicFieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\BasicFieldMapperGetInstance;

class BasicFieldMapper implements BasicFieldMapperInterface
{
    use BasicFieldMapperConstruct;
    use BasicFieldMapperGetInstance;

    public function map(): array
    {
        return [
            'type' => $this->type,
            'view' => $this->type,
            'id' => $this->field,
            'name' => $this->field,
            'disabled' => true,
            'classList' => $this->createClassList(),
            'attributeList' => $this->createAttributeList(),
            'fieldAttributeList' => [
                'data-js-validation-message-value-missing' => $this->lang->errorRequired,
            ],
        ];
    }

    private function createClassList(): array
    {
        $classList = ['mod-frontend-form__field', 'o-layout-grid--col-span-12'];

        return $classList;
    }

    private function createAttributeList(): array
    {
        $attributeList = [
            'data-js-field' => $this->type,
            'data-js-field-name' => $this->field,
            'data-js-conditional-logic' => "{}"
        ];

        return $attributeList;
    }
}