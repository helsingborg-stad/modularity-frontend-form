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

    public function map(): array
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
            'id'          => $this->field['wrapper']['id'] ?: '',
            'disabled'    => true,
            'attributeList' => $this->createAttributeList($conditionalLogicMapper),
            'fieldAttributeList' => [
                'data-js-validation-message-value-missing' => 'This field is required.'
            ],
            'classList' => explode(' ', $this->field['wrapper']['class'] ?? []),
        ];
    }

    /**
     * Create attribute list for field
     */
    private function createAttributeList(ConditionalLogicMapper $conditionalLogicMapper): array
    {
        $attributeList = [
            'data-js-conditional-logic' => $conditionalLogicMapper->map() ?? "{}",
            'data-js-field' => $this->type,
            'data-js-field-name' => $this->field['key']
        ];

        return $attributeList;
    }
}
