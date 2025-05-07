<?php 

namespace ModularityFrontendForm\FieldMapping\Director;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;

use ModularityFrontendForm\FieldMapping\Mapper\TextFieldMapper;

class FieldMappingDirector implements FieldMappingDirectorInterface
{
    protected array $mapperMap = [
        'text' => TextFieldMapper::class,
    ];

    public function resolveMapper(array $field): FieldMapperInterface
    {
        $type = $field['type'] ?? 'text';
        $mapperClass = $this->mapperMap[$type] ?? TextFieldMapper::class;

        if (!is_subclass_of($mapperClass, FieldMapperInterface::class)) {
            throw new \RuntimeException("Invalid mapper class: {$mapperClass}");
        }

        return $mapperClass::getInstance($field);
    }
}