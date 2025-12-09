<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

use WpService\WpService;
use ModularityFrontendForm\Config\Config;

trait FieldMapperGetInstance
{
    public static function getInstance(array|string $field, WpService $wpService, object $lang, Config $config): static
    {
        return new static($field, $wpService, $lang, $config);
    }
}