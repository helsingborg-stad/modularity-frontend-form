<?php 

namespace ModularityFrontendForm\DataProcessor\Handlers;


use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Api\RestApiParamsInterface;
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

    /**
     * Creates an array of handlers for the given module ID
     *
     * @param int $moduleId The module ID
     *
     * @return HandlerInterface[] An array of handlers
     */
    public function createHandlers(object $params): array {
        $handlers       = [];
        $moduleConfig   = $this->getModuleConfigInstance($params->moduleId);
        $activeHandlers = $moduleConfig->getActivatedHandlers();

        $args = $this->createHandlerInterfaceRequiredArguments($params);

        foreach ($activeHandlers as $handler) {
            switch ($handler) {
                case 'WpDbHandler':
                    $handlers[] = new WpDbHandler(...$args);
                    break;
                case 'MailHandler':
                    $handlers[] = new MailHandler(...$args);
                    break;
                case 'WebHookHandler':
                    $handlers[] = new WebHookHandler(...$args);
                    break;
            }
        }

        return $handlers;
    }

    /**
     * Creates a null handler that does nothing and returns an error.
     * This is used when no handler is found for the given data.
     *
     * @param int $moduleId The module ID
     *
     * @return HandlerInterface A null handler
     */
    public function createNullHandler(object $params): HandlerInterface {
        return new NullHandler(...$this->createHandlerInterfaceRequiredArguments(
            $params
        ));
    }

    /**
     * Creates a array representing the arguments for the validator
     * 
     * @param int $moduleId The module ID
     * 
     * @return array An array of arguments
     */
    private function createHandlerInterfaceRequiredArguments(object $params): array {
        return [
            $this->wpService,
            $this->acfService,
            $this->config,
            $this->getModuleConfigInstance($params->moduleId),
            $params
        ];
    }
}