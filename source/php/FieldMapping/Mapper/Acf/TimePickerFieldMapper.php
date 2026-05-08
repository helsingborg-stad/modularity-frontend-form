<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TimePickerFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'time'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?? null;
        $mapped['value']                               = $this->field['default_value'] ?? null;
        $mapped['minTime']                             = $this->field['min_time'] ?? null;
        $mapped['maxTime']                             = $this->field['max_time'] ?? null;
        $mapped['moveAttributesListToFieldAttributes'] = false;

        $mapped['fieldAttributeList']['data-js-validation-message-type-mismatch'] = $this->lang->errorTime;
        $mapped['fieldAttributeList']['data-js-validation-message-value-missing'] = $this->lang->errorTime;

        if (!empty($this->field['min_time'])) {
            $mapped['fieldAttributeList']['data-js-validation-message-range-underflow'] = sprintf(
                $this->lang->errorTimeRangeUnderflow,
                $this->field['min_time']
            );
        }

        if (!empty($this->field['max_time'])) {
            $mapped['fieldAttributeList']['data-js-validation-message-range-overflow'] = sprintf(
                $this->lang->errorTimeRangeOverflow,
                $this->field['max_time']
            );
        }

        return $mapped;
    }
}