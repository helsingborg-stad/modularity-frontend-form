<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

use WpService\WpService;

trait FieldMapperGetInstance
{
    public static function getInstance(array|string $field, WpService $wpService, object $lang): static
    {
        return new static($field, $wpService, $lang);
    }
}