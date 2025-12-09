<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Wordpress;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;

class PostTitle implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'text'))->map();

        $mapped['name'] = $this->config->getFieldNamespace($this->field);
        $mapped['label'] = $this->wpService->__('Post Title', 'modularity-frontend-form');
        $mapped['required'] = true;
        $mapped['moveAttributesListToFieldAttributes'] = false;
        $mapped['fieldAttributeList'] = [
            'data-js-validation-message-value-missing' => $this->lang->errorRequired,
        ];

        return $mapped;
    }
}