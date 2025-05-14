<?php 

namespace ModularityFrontendForm\DataProcessor\Handlers;


use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\NullHandler;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\DataProcessor\Handlers\WpDbHandler;
use ModularityFrontendForm\DataProcessor\Handlers\MailHandler;

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
        $handlers       = [];
        $moduleConfig   = $this->getModuleConfigInstance($moduleId);
        $activeHandlers = $moduleConfig->getActivatedHandlers();

        $args = $this->createHandlerInterfaceRequiredArguments($moduleId);
        $avabileHandlers = [
            'WpDbHandler' => new WpDbHandler(...$args),
            'MailHandler' => new MailHandler(...$args),
        ];
        
        foreach ($activeHandlers as $handler) {
            if (array_key_exists($handler, $avabileHandlers)) {
                $handlers[] = $avabileHandlers[$handler];
            }
        }

        return $handlers;
    }

    public function createNullHandler(int $moduleId): HandlerInterface {
        return new NullHandler(...$this->createHandlerInterfaceRequiredArguments(
            $moduleId
        ));
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