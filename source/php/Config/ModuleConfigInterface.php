<?php 

namespace ModularityFrontendForm\Config;

use AcfService\AcfService;
use WpService\WpService;

interface ModuleConfigInterface
{
    public function __construct(
        WpService $wpService,
        AcfService $acfService,
        ConfigInterface $config,
        int $moduleId,
    );

    public function getModuleId(): int;
    public function getModuleSlug(): string;
    public function getModuleIsSubmittable(): bool;
    public function getTargetPostType(): string;
    public function getTargetPostStatus(): string;
    public function getNonceKey(): string;

    public function getActivatedHandlers(): array;
    public function getWpDbHandlerConfig(): object;
    public function getMailHandlerConfig(): object;
    public function getWebHookHandlerConfig(): object;
}