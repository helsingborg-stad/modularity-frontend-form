<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class WysiwygFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'wysiwyg'))->map();

        if (!empty($this->field['required'])) {
            $mapped['attributeList']['data-js-required'] = 'required';
            unset($mapped['required']);
        }

        $mapped['classList'][] = 'mod-frontend-form__wysiwyg';
        echo '<pre>' . print_r( $mapped, true ) . '</pre>';die;
        return $mapped;
    }
}