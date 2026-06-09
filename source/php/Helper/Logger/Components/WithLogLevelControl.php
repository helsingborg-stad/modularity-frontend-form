<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\LogLevelPrio;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;

class WithLogLevelControl extends NullLogger implements LoggerInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private int $logLevel = 500
    ) {}

    public function log($level, string|Stringable $message, array $context = []): void
    {
        LogLevelPrio::LEVELS[$level] >= $this->logLevel
            && $this->logger->log($level, $message, [...$context]);
    }
}
