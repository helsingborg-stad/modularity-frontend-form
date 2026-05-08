<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

trait BasicFieldMapperGetInstance
{
    public static function getInstance(array|string $field, object $lang, ?string $type = null): static
    {
        return new static($field, $lang, $type);
    }
}