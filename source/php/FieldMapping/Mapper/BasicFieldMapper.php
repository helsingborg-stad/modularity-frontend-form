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
            'id'          => $this->field['wrapper']['id'] ?: $this->field['key'],
            'disabled'    => true,
            'attributeList' => $this->createAttributeList($conditionalLogicMapper),
            'fieldAttributeList' => [
                'data-js-validation-message-value-missing' => 'This field is required.'
            ],
            'classList' => $this->createClassList(),
        ];
    }

    private function createClassList(): array
    {

        $classList =  explode(' ', $this->field['wrapper']['class'] ?? '');
        $classList[] = 'mod-frontend-form__field';

        if (!empty($this->field['wrapper']['width'])) {
            $classList[] = 'o-layout-grid--col-span-' . $this->calculateColumnSpan((int) $this->field['wrapper']['width']) . '@cq-lg';
        }

        $classList[] = 'o-layout-grid--col-span-12';

        return $classList;
    }

    /**
     * Calculate column span based on width percentage
     * 
     * @param int $widthPercentage
     * @return int
     */
    private function calculateColumnSpan(int $widthPercentage): int
    {
        $widthPercentage = max(0, min(100, $widthPercentage));
        $span = (int) round(($widthPercentage / 100) * 12);

        if ($widthPercentage > 0 && $span < 1) {
            $span = 1;
        }

        return min($span, 123);
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
