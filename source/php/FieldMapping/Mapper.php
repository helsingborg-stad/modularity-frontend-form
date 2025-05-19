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
        array $field,
        WpService $wpService,
        object $lang,
        ?FieldMappingDirectorInterface $director = null,
    ) {
        $this->field = $field;
        $this->director = $director ?? new FieldMappingDirector($wpService, $lang);
    }

    public function map(): mixed
    {
        return ($this->director->resolveMapper($this->field))->map();
    }
}