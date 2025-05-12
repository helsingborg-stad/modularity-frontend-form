<?php 

namespace ModularityFrontendForm\Config;

use WpService\WpService;

interface ConfigInterface
{
    public function __construct(
        WpService $wpService, 
        string $filterPrefix,
    );

    public function getModuleSlug(): string;
    public function getFieldNamespace(): string;
    public function getFilterPrefix(): string;
    public function createFilterKey(string $filter = ""): string;
}