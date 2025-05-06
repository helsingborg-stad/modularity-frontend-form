<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\FieldMapperInterface;

class TextFieldMapper implements FieldMapperInterface
{
    protected array $field;

    protected function __construct(array $field)
    {
        $this->field = $field;
    }

    public static function getInstance(array $field): self
    {
        static $instance = null;
        if (!$instance) {
            $instance = new self($field);
        }
        return $instance;
    }

    public function map(): mixed
    {
        return $this->field['value'] ?? null;
    }
}