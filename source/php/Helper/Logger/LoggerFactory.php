<?php

namespace ModularityFrontendForm\Helper\Logger;

use ModularityFrontendForm\Helper\Logger\Components\WithBaseLogger;
use ModularityFrontendForm\Helper\Logger\Components\WithComposite;
use ModularityFrontendForm\Helper\Logger\Components\WithFormatter;
use ModularityFrontendForm\Helper\Logger\Components\WithLogLevelControl;
use ModularityFrontendForm\Helper\Logger\Contracts\LoggerFactoryInterface;
use ModularityFrontendForm\Helper\Logger\LogLevelPrio;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class LoggerFactory implements LoggerFactoryInterface
{
    public function __construct(private $namespace = 'plugin-name', private $loggers = [[
        'logger'    => new NullLogger, 
        'logLevel'  => LogLevel::ERROR
    ]]){ }

    public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface
    {
        return new WithBaseLogger(
            new WithComposite(
                array_map(
                    fn($logger) => $this->composeFromConfig([
                        ...$logger, 
                        ...$args
                    ]), 
                    $this->loggers
                )
            ),
            $this
        );
    }

    private function composeFromConfig(array $args): LoggerInterface {
        return new WithLogLevelControl(
            new WithFormatter(
                $args['logger'],
                $args['namespace'] ?? $this->namespace
            ),
            LogLevelPrio::LEVELS[$args['logLevel'] ?? LogLevel::ERROR] ?? LogLevelPrio::LEVELS[LogLevel::ERROR] 
        );
    }
}

