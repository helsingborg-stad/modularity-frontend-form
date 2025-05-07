<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\BasicFieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\ConditionalLogicMapper;

class BasicFieldMapper extends AbstractFieldMapper implements BasicFieldMapperInterface 
{
    public function map(): mixed
    {
      $conditionalLogicMapper = new ConditionalLogicMapper(
        $this->field, 'conditional_logic'
      );

      return [
          'type'        => $this->type,
          'view'        => $this->type,
          'label'       => $this->field['label'],
          'name'        => $this->field['key'],
          'required'    => $this->field['required'] ?? false,
          'description' => $this->field['instructions'] ?? '',
          'attributeList' => [
              'data-js-conditional-logic' => $conditionalLogicMapper->map(),
              'data-js-field' => $this->type,
              'data-js-field-name' => $this->field['key'],
          ]
      ];
      
    }
}