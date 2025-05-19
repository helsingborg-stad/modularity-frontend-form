<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class UrlFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $mapped = (new BasicFieldMapper($this->field, 'url'))->map();

        if (is_array($mapped)) {
            $mapped['placeholder']                         = $this->field['placeholder'] ?? '';
            $mapped['value']                               = $this->field['default_value'] ?? '';
            $mapped['fieldAttributeList']['data-js-validation-message-type-mismatch'] = $this->lang->errorUrl;
            $mapped['moveAttributesListToFieldAttributes'] = false;
        }

        return $mapped ?? null;
    }
}