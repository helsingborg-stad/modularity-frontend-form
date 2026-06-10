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
        $this->namespacePath = $this->effectivePath($namespace);
    }

    public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface
    {
        return new WithBaseLogger(
            new WithComposite(
                array_map(
                    fn($loggerConfiguration) => 
                        $this->composeFromConfig([
                            ...['logLevel' => $this->logLevel, 'namespace' => $this->namespace],
                            ...$loggerConfiguration,
                            ...$this->reduceToOverridables($args)
                        ]),
                    $this->loggers
                )
            ),
            new self($args['namespace'] ?? $this->namespace, $this->loggers, $this->namespacePath, $this->logLevel)
        );
    }

    private function composeFromConfig(array $args): LoggerInterface {
        return new WithLogLevelControl(
            new WithContextPlaceholders(
                new WithFormatter(
                        $args['logger'],
                        $this->effectivePath($args['namespace'] ?? $this->namespace),
                        $args['breadcrumbDirection'] ?? null,
                        $args['breadcrumbMaxCount'] ?? null,
                ),
            ),
            LogLevelPrio::LEVELS[$args['logLevel'] ?? LogLevel::ERROR] ?? LogLevelPrio::LEVELS[LogLevel::ERROR]
        );
    }

    private function reduceToOverridables(array $args)
    {
        unset($args['logger']);
        unset($args['logLevel']);
        return $args;
    }

    private function effectivePath(string $namespace)
    {
        $effectivePath  = $this->namespacePath;
        if (empty($effectivePath) || $effectivePath[array_key_last($effectivePath)] !== $namespace) {
            $effectivePath[] = $namespace;
        }

        return $effectivePath;
    }
}

