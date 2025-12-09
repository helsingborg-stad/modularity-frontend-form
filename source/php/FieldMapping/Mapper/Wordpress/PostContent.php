<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Wordpress;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;

class PostContent implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'wysiwyg'))->map();
        $mapped['name'] = $this->config->getFieldNamespace($this->field);
        $mapped['label'] = $this->wpService->__('Post Content', 'modularity-frontend-form');
        $mapped['attributeList']['data-js-required'] = 'required';
        $mapped['classList'][] = 'mod-frontend-form__wysiwyg';

        return $mapped;
    }
}