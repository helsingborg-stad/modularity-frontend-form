<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

trait FieldMapperGetInstance
{
    public static function getInstance(array $field): static
    {
        return new static($field);
    }
}