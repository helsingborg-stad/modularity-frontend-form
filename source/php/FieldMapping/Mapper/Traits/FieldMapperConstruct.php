<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

use WpService\WpService;

trait FieldMapperConstruct
{
    public function __construct(protected array $field, protected WpService $wpService){}
}