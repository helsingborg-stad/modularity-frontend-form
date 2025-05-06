<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

interface FieldMapperInterface
{
    public static function getInstance(array $field): self;
    public function map(): mixed;
}