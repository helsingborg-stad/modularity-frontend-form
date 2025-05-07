<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

abstract class AbstractFieldMapper implements Interfaces\FieldMapperInterface
{
    public function __construct(protected array $field){}

    public static function getInstance(array $field): static
    {
        return new static($field);
    }
}