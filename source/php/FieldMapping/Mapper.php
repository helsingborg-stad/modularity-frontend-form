<?php

namespace ModularityFrontendForm\FieldMapping;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirector;
use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use WpService\WpService;

class Mapper
{
    protected array $field;
    protected FieldMappingDirectorInterface $director;

    public function __construct(
        WpService $wpService,
        object $lang,
        ?FieldMappingDirectorInterface $director = null,
    ) {
        $this->director = $director ?? new FieldMappingDirector($wpService, $lang);
    }

    public function map(string|array $field): mixed
    {
        return ($this->director->resolveMapper($field))->map();
    }
}