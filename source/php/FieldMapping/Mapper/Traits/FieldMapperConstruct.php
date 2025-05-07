<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

trait FieldMapperConstruct
{
    protected array $field;

    public function __construct(array $field)
    {
        $this->field = $field;
    }
}