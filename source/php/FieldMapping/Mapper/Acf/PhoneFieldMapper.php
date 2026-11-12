<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

class PhoneFieldMapper extends TextFieldMapper
{
    public function map(): array
    {
        $mapped = parent::map();
        $mapped['type'] = 'tel';
        $mapped['view'] = 'phone';
        $mapped['attributeList']['data-js-field'] = 'text';
        $mapped['autocomplete'] = 'tel';
        $mapped['description'] = $mapped['description'] ?: $this->wpService->__(
            'For international numbers, include the country code with + or 00.',
            'acf-phone-number-field'
        );
        $mapped['phoneHelpId'] = $mapped['id'] . '-phone-help';
        $mapped['fieldAttributeList']['aria-describedby'] = $mapped['phoneHelpId'];
        return $mapped;
    }
}
