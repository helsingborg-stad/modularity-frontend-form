<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

use WpService\WpService;

trait FieldMapperGetInstance
{
    public static function getInstance(array $field, WpService $wpService): static
    {
        return new static($field, $wpService);
    }
}