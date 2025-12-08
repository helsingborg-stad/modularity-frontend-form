<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

use WpService\WpService;

interface FieldMapperInterface
{
    public static function getInstance(array|string $field, WpService $wpService, object $lang): self;
    public function map(): array;
}