<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\BasicFieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class BasicFieldMapper implements BasicFieldMapperInterface
{
    public function __construct(protected array $field, private ?string $type = null) {}

    public static function getInstance(array $field, ?string $type = null): self
    {
        return new static($field, $type);
    }

    public function map(): ?array
    {
        $conditionalLogicMapper = new ConditionalLogicMapper(
            $this->field,
            'conditional_logic'
        );

        return [
            'type'        => $this->type,
            'view'        => $this->type,
            'label'       => $this->field['label'],
            'name'        => $this->field['key'],
            'required'    => $this->field['required'] ?: false,
            'description' => $this->field['instructions'] ?: '',
            'disabled'    => true,
            'attributeList' => [
                'data-js-conditional-logic' => $conditionalLogicMapper->map() ?? "{}",
                'data-js-field' => $this->type,
                'data-js-field-name' => $this->field['key'],
            ],
            'fieldAttributeList' => [
                'data-js-validation-message-pattern-mismatch' => 'some text',
                'pattern' => '[^a]',
            ]
        ];
    }
}
