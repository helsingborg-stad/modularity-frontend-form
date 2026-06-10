<?php

namespace ModularityFrontendForm\Helper\Logger;

use ModularityFrontendForm\Helper\Logger\Components\WithBaseLogger;
use ModularityFrontendForm\Helper\Logger\Components\WithComposite;
use ModularityFrontendForm\Helper\Logger\Components\WithContextPlaceholders;
use ModularityFrontendForm\Helper\Logger\Components\WithFormatter;
use ModularityFrontendForm\Helper\Logger\Components\WithLogLevelControl;
use ModularityFrontendForm\Helper\Logger\Contracts\LoggerFactoryInterface;
use ModularityFrontendForm\Helper\Logger\LogLevelPrio;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class LoggerFactory implements LoggerFactoryInterface
{
    public function __construct(
        private string $namespace = 'PluginNameSpace', 
        private $loggers = [
            [
                'logger'    => new NullLogger, 
                'logLevel'  => LogLevel::ERROR
            ]
        ],
        private array $namespacePath = [],
        private string $logLevel = LogLevel::ERROR
    )  {
        if (empty($namespacePath) || $namespacePath[array_key_last($namespacePath)] !== $namespace) $this->namespacePath[] = $namespace;
    }

    public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface
    {
        return new WithBaseLogger(
            new WithComposite(
                array_map(
                    fn($logger) => $this->composeFromConfig([
                        ...['logLevel' => $this->logLevel],
                        ...$logger,
                        ...$this->toOnlyOverridable($args)
                    ]), 
                    $this->loggers
                )
            ),
            new self($args['namespace'] ?? $this->namespace,$this->loggers, $this->namespacePath, $this->logLevel)
        );
    }

    private function composeFromConfig(array $args): LoggerInterface {
        return new WithLogLevelControl(
            new WithContextPlaceholders(
                new WithFormatter(
                        $args['logger'],
                        $this->namespacePath
                    ),
            ),
            LogLevelPrio::LEVELS[$args['logLevel'] ?? LogLevel::ERROR] ?? LogLevelPrio::LEVELS[LogLevel::ERROR] 
        );
    }

    private function toOnlyOverridable(array $args)
    {
        unset($args['logger']);
        unset($args['logLevel']);

        return $args;
    }
}

