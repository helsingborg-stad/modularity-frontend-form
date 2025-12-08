<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Traits;

trait BasicFieldMapperConstruct
{
    public function __construct(
        protected string|array $field,
        private object $lang,
        private ?string $type = null
    ) {}
}