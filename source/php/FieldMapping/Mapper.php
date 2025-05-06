<?php

namespace ModularityFrontendForm\FieldMapping;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirector;
use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;

class Mapper
{
    protected array $field;
    protected FieldMappingDirectorInterface $director;

    public function __construct(array $field, ?FieldMappingDirectorInterface $director = null)
    {
        $this->field = $field;
        $this->director = $director ?? new FieldMappingDirector();
    }

    public function map(): mixed
    {
        return ($this->director->resolveMapper($this->field))->map();
    }
}