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
        static $index = 0;

        $field = [
            'type' => 'wysiwyg',
            'view' => 'wysiwyg',
            'label' => $this->wpService->__('Post Content', 'modularity-frontend-form'),
            'id' => 'post_content',
            'name' => 'post_content',
            'classList' => ['mod-frontend-form__wysiwyg', 'mod-frontend-form__field', 'o-layout-grid--col-span-12'],
            'attributeList' => []
        ];

        $index++;
        echo '<pre>' . print_r( $field, true ) . '</pre>';
        return $field;
    }
}