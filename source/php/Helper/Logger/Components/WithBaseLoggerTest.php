<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Contracts\LoggerFactoryInterface;
use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use ModularityFrontendForm\Helper\Logger\NullLoggerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class WithBaseLoggerTest extends TestCase
{
    private function createSpyFactory(): LoggerFactoryInterface
    {
        return new class extends NullLoggerFactory {
            public array $calls = [];

            public function createLogger(array $args = []): LoggerInterface&LoggerFactoryInterface
            {
                $this->calls[] = $args;
                return parent::createLogger($args);
            }
        };
    }

    /**
     * @testdox uses NullLogger by default when no logger is injected
     */
    public function testDefaultsToNullLogger(): void
    {
        $logger = new WithBaseLogger();

        // Should not throw — NullLogger silently discards messages
        $logger->info('test');
        $this->addToAssertionCount(1);
    }

    /**
     * @testdox log() delegates to the injected logger with the same level, message, and context
     */
    public function testLogDelegatesToInjectedLogger(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->log(LogLevel::ERROR, 'something went wrong', ['key' => 'value']);

        $this->assertCount(1, $spy->records);
        $this->assertSame(LogLevel::ERROR, $spy->records[0]['level']);
        $this->assertSame('something went wrong', $spy->records[0]['message']);
        $this->assertSame(['key' => 'value'], $spy->records[0]['context']);
    }

    /**
     * @testdox emergency() calls log() with EMERGENCY level
     */
    public function testEmergencyDelegatesToLogWithEmergencyLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->emergency('emergency message');

        $this->assertSame(LogLevel::EMERGENCY, $spy->records[0]['level']);
        $this->assertSame('emergency message', $spy->records[0]['message']);
    }

    /**
     * @testdox alert() calls log() with ALERT level
     */
    public function testAlertDelegatesToLogWithAlertLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->alert('alert message');

        $this->assertSame(LogLevel::ALERT, $spy->records[0]['level']);
    }

    /**
     * @testdox critical() calls log() with CRITICAL level
     */
    public function testCriticalDelegatesToLogWithCriticalLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->critical('critical message');

        $this->assertSame(LogLevel::CRITICAL, $spy->records[0]['level']);
    }

    /**
     * @testdox error() calls log() with ERROR level
     */
    public function testErrorDelegatesToLogWithErrorLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->error('error message');

        $this->assertSame(LogLevel::ERROR, $spy->records[0]['level']);
    }

    /**
     * @testdox warning() calls log() with WARNING level
     */
    public function testWarningDelegatesToLogWithWarningLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->warning('warning message');

        $this->assertSame(LogLevel::WARNING, $spy->records[0]['level']);
    }

    /**
     * @testdox notice() calls log() with NOTICE level
     */
    public function testNoticeDelegatesToLogWithNoticeLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->notice('notice message');

        $this->assertSame(LogLevel::NOTICE, $spy->records[0]['level']);
    }

    /**
     * @testdox info() calls log() with INFO level
     */
    public function testInfoDelegatesToLogWithInfoLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->info('info message');

        $this->assertSame(LogLevel::INFO, $spy->records[0]['level']);
    }

    /**
     * @testdox debug() calls log() with DEBUG level
     */
    public function testDebugDelegatesToLogWithDebugLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->debug('debug message');

        $this->assertSame(LogLevel::DEBUG, $spy->records[0]['level']);
    }

    /**
     * @testdox log() passes context array through to the inner logger
     */
    public function testLogPassesContextToInnerLogger(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->info('msg', ['foo' => 'bar', 'baz' => 42]);

        $this->assertSame(['foo' => 'bar', 'baz' => 42], $spy->records[0]['context']);
    }

    /**
     * @testdox createLogger() returns an object implementing LoggerInterface
     */
    public function testCreateLoggerReturnsLoggerInterface(): void
    {
        $logger = new WithBaseLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger->createLogger());
    }

    /**
     * @testdox createLogger() returns an object implementing LoggerFactoryInterface
     */
    public function testCreateLoggerReturnsLoggerFactoryInterface(): void
    {
        $logger = new WithBaseLogger();

        $this->assertInstanceOf(LoggerFactoryInterface::class, $logger->createLogger());
    }

    /**
     * @testdox createLogger() delegates to the injected factory
     */
    public function testCreateLoggerDelegatesToInjectedFactory(): void
    {
        $spy = $this->createSpyFactory();
        $logger = new WithBaseLogger(new NullLogger(), $spy);

        $logger->createLogger();

        $this->assertCount(1, $spy->calls);
    }

    /**
     * @testdox createLogger() passes args through to the factory
     */
    public function testCreateLoggerPassesArgsToFactory(): void
    {
        $spy = $this->createSpyFactory();
        $logger = new WithBaseLogger(new NullLogger(), $spy);

        $logger->createLogger(['namespace' => 'custom']);

        $this->assertSame([['namespace' => 'custom']], $spy->calls);
    }
}
