<?php

namespace ModularityFrontendForm\FieldMapping;

use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirector;
use ModularityFrontendForm\FieldMapping\Director\FieldMappingDirectorInterface;
use WpService\WpService;
use ModularityFrontendForm\Config\Config;

class Mapper
{
    protected array $field;
    protected FieldMappingDirectorInterface $director;

    public function __construct(
        WpService $wpService,
        object $lang,
        protected Config $config,
        ?FieldMappingDirectorInterface $director = null
    ) {
        $this->director = $director ?? new FieldMappingDirector($wpService, $lang, $config);
    }

    public function map(string|array $field): mixed
    {
        return ($this->director->resolveMapper($field))->map();
    }
}