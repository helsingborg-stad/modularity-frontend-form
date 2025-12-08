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
        return [
            'type' => 'text',
            'view' => 'text',
            'label' => $this->wpService->__('Post Title', 'modularity-frontend-form'),
            'name' => 'post_title',
            'required' => true,
            'fieldAttributeList' => [
                'data-js-validation-message-value-missing' => $this->lang->errorRequired,
            ],
            'classList' => ['o-layout-grid--col-span-12'],
        ];
    }
}