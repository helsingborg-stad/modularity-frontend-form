<?php

namespace ModularityFrontendForm\Helper\Logger;

use ModularityFrontendForm\Helper\Logger\Contracts\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NullLoggerFactory extends NullLogger implements LoggerFactoryInterface
{
    public function __construct(private $args = []) {}

    public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface
    {
        return new self();
    }
}
