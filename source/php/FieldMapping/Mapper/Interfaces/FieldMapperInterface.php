<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

use WpService\WpService;

interface FieldMapperInterface
{
    public static function getInstance(array $field, WpService $wpService): self;
    public function map(): ?array;
}