<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class NumberFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): array
    {
        $mapped = (new BasicFieldMapper($this->field, $this->lang, 'number'))->map();

        $mapped['placeholder']                         = $this->field['placeholder'] ?: '';
        $mapped['value']                               = $this->field['default_value'] ?: '';
        $mapped['moveAttributesListToFieldAttributes'] = false;

        $mapped['fieldAttributeList']['step'] = isset($this->field['step']) ? $this->field['step'] : 'any';

        if (!empty($this->field['step'])) {
            $mapped['fieldAttributeList']['data-js-validation-message-step-mismatch'] = sprintf(
                $this->lang->errorNumberStepMismatch,
                $this->field['step']
            );
        }

        if (!empty(($this->field['min'])) || $this->field['min'] === 0) {
            $mapped['fieldAttributeList']['min'] = $this->field['min'];
            $mapped['fieldAttributeList']['data-js-validation-message-range-underflow'] = sprintf(
                $this->lang->errorNumberUnderflow,
                $this->field['min']
            );
        }

        if (isset($this->field['max'])) {
            $mapped['fieldAttributeList']['max'] = $this->field['max'];
            $mapped['fieldAttributeList']['data-js-validation-message-range-overflow'] = sprintf(
                $this->lang->errorNumberOverflow,
                $this->field['max']
            );
        }

        $mapped['fieldAttributeList']['data-js-validation-message-type-mismatch'] = $this->lang->errorNumber;
        $mapped['fieldAttributeList']['data-js-validation-message-value-missing'] = $this->lang->errorNumber;
        $mapped['fieldAttributeList']['data-js-validation-message-bad-input'] = $this->lang->errorNumber;

        return $mapped;
    }
}