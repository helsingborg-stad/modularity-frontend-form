<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

interface BasicFieldMapperInterface
{
    public static function getInstance(array|string $field, object $lang, ?string $type = null): self;
    public function map(): array;
}