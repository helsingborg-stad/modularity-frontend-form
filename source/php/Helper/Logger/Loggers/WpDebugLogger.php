<?php

namespace ModularityFrontendForm\Helper\Logger\Loggers;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;

class WpDebugLogger extends NullLogger implements LoggerInterface
{
    public function log($level, string|Stringable $message, array $context = []): void
    {
        error_log($message);
    }
}