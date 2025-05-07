<?php 

namespace ModularityFrontendForm\FieldMapping\Director;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;

use ModularityFrontendForm\FieldMapping\Mapper\TextFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\EmailFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\UrlFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\TextareaFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\TrueFalseFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\SelectFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\CheckboxFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\MessageFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\FileFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\NumberFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\ImageFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\RadioFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\RepeaterFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\TimePickerFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\DatePickerFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\ButtonGroupFieldMapper;

class FieldMappingDirector implements FieldMappingDirectorInterface
{
    protected array $mapperMap = [
        'text'          => TextFieldMapper::class,
        'email'         => EmailFieldMapper::class,
        'url'           => UrlFieldMapper::class,
        'textarea'      => TextareaFieldMapper::class,
        'true_false'    => TrueFalseFieldMapper::class,
        'select'        => SelectFieldMapper::class,
        'checkbox'      => CheckboxFieldMapper::class,
        'message'       => MessageFieldMapper::class,
        'file'          => FileFieldMapper::class,
        'number'        => NumberFieldMapper::class,
        'image'         => ImageFieldMapper::class,
        'radio'         => RadioFieldMapper::class,
        'repeater'      => RepeaterFieldMapper::class,
        'time_picker'   => TextFieldMapper::class,
        'date_picker'   => TextFieldMapper::class,
        'button_group'  => ButtonGroupFieldMapper::class
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