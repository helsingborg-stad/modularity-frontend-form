<?php 

namespace ModularityFrontendForm\Config;

use AcfService\AcfService;
use WpService\WpService;

interface ConfigInterface
{
    public function __construct(
        WpService $wpService, 
        string $filterPrefix,
    );

    public function getModuleSlug(): string;
    public function getNonceKey(): string;
    public function getFilterPrefix(): string;
    public function createFilterKey(string $filter = ""): string;
}