<?php 

namespace ModularityFrontendForm\Config;

use WpService\WpService;

interface ConfigInterface
{

    public function getModuleSlug(): string;
    public function getFieldNamespace(): string;
    public function getMetaDataNamespace(?string $key = null): string;
    public function getFilterPrefix(): string;
    public function getUnprintableKeys(): array;
    public function getKeysToBypass(): array;
    public function getAllowedHtmlTags(): array;
    public function createFilterKey(string $filter = ""): string;
}