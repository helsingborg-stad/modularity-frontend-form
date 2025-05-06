<?php

namespace ModularityFrontendForm\FieldMapping\Director;
use ModularityFrontendForm\FieldMapping\Mapper\FieldMapperInterface;

interface FieldMappingDirectorInterface
{
    public function resolveMapper(array $field): FieldMapperInterface;
}