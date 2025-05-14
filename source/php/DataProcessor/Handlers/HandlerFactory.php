<?php 

namespace ModularityFrontendForm\DataProcessor\Handlers;


use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\NullHandler;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;

class HandlerFactory {

    use GetModuleConfigInstanceTrait;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigFactoryInterface $moduleConfigFactory
    ) {
    }

    public function createHandlers(int $moduleId): array {
        $args = $this->createHandlerInterfaceRequiredArguments($moduleId);
        return [
            new NullHandler(...$args),
        ];
    }

    /**
     * Creates a array representing the arguments for the validator
     */
    private function createHandlerInterfaceRequiredArguments(int $moduleId): array {
        return [
            $this->wpService,
            $this->acfService,
            $this->config,
            $this->getModuleConfigInstance($moduleId),
        ];
    }
}