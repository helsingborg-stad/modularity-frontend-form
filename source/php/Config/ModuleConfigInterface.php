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
    public function getModuleTitle(): string;
    public function getModuleIsSubmittableByCurrentUser(): bool;
    public function getModuleIsEditable(): bool;
    public function getNonceKey(): string;

    public function getFieldKeysRegisteredAsFormFields(): ?array;

    public function getActivatedHandlers(): array;
    public function getWpDbHandlerConfig(): ?object;
    public function getMailHandlerConfig(): ?object;
    public function getWebHookHandlerConfig(): ?object;
}