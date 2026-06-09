<?php

namespace ModularityFrontendForm\Helper\Logger\Contracts;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface;
}