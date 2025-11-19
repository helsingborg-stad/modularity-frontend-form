<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class EmailFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'email'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?: '';
        $mapped['value']                               = $this->field['default_value'] ?: '';

        $errorMessage = sprintf(
            $this->lang->errorEmail,
            'example@example.com'
        );

        $mapped['fieldAttributeList']['data-js-validation-message-type-mismatch'] = $errorMessage;
        $mapped['fieldAttributeList']['data-js-validation-message-value-missing'] = $errorMessage;

        $mapped['moveAttributesListToFieldAttributes'] = false;

        return $mapped;
    }
}