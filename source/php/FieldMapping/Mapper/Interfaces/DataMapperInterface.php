<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

interface DataMapperInterface extends \ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface
{
  public static function getInstance(array $field, string $subkey): self;
}