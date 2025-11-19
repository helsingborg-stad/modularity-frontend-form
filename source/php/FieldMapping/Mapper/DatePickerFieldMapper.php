<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class DatePickerFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'date'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?? null;
        $mapped['value']                               = $this->field['default_value'] ?? null;
        $mapped['minDate']                             = $this->field['min_date'] ?? null;
        $mapped['maxDate']                             = $this->field['max_date'] ?? null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        $mapped['fieldAttributeList']['data-js-validation-message-type-mismatch'] = $this->lang->errorDate;
        $mapped['fieldAttributeList']['data-js-validation-message-value-missing'] = $this->lang->errorDate;

        if (!empty($this->field['min_date'])) {
            $mapped['fieldAttributeList']['data-js-validation-message-range-underflow'] = sprintf(
                $this->lang->errorDateRangeUnderflow,
                $this->field['min_date']
            );
        }

        if (!empty($this->field['max_date'])) {
            $mapped['fieldAttributeList']['data-js-validation-message-range-overflow'] = sprintf(
                $this->lang->errorDateRangeOverflow,
                $this->field['max_date']
            );
        }

        return $mapped;
    }
}