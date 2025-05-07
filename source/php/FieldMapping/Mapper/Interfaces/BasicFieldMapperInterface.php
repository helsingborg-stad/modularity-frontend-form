<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

interface BasicFieldMapperInterface
{
    public static function getInstance(array $field, ?string $type = null): self;
    public function map(): ?array;
}