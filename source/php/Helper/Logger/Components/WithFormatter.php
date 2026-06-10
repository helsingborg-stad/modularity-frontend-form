<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Stringable;

class WithFormatter extends NullLogger implements LoggerInterface
{
    public function __construct(private LoggerInterface $logger, private string $namespace)
    {}

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $args = [
            \strtoupper($level), 
            $this->namespace,
            $message
        ];

        $this->logger->log($level, \sprintf("[%s] [%s] %s", ...$args),$context);
    }
}