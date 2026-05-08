<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Interfaces;

use WpService\WpService;
use ModularityFrontendForm\Config\Config;

interface FieldMapperInterface
{
    public static function getInstance(array|string $field, WpService $wpService, object $lang, Config $config): self;
    public function map(): array;
}