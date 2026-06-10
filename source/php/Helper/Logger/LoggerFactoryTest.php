<?php

namespace ModularityFrontendForm\Helper\Logger;

use ModularityFrontendForm\Helper\Logger\Contracts\LoggerFactoryInterface;
use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class LoggerFactoryTest extends TestCase
{
    /**
     * @testdox default log level is ERROR — messages at ERROR and above are forwarded
     */
    public function testDefaultLogLevelAllowsErrorAndAbove(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy]]))->createLogger();

        foreach ([LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::ALERT, LogLevel::EMERGENCY] as $level) {
            $logger->log($level, $level);
        }

        $this->assertCount(4, $spy->records);
    }

    /**
     * @testdox default log level is ERROR — messages below ERROR are suppressed
     */
    public function testDefaultLogLevelSuppressesBelowError(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy]]))->createLogger();

        foreach ([LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG] as $level) {
            $logger->log($level, $level);
        }

        $this->assertCount(0, $spy->records);
    }

    /**
     * @testdox custom logLevel registered via constructor is respected
     */
    public function testCustomLogLevelIsRespected(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger();

        foreach ([
            LogLevel::DEBUG, LogLevel::INFO, LogLevel::NOTICE,
            LogLevel::WARNING, LogLevel::ERROR, LogLevel::CRITICAL,
            LogLevel::ALERT, LogLevel::EMERGENCY,
        ] as $level) {
            $logger->log($level, $level);
        }

        $this->assertCount(8, $spy->records);
    }

    /**
     * @testdox message is formatted as [LEVEL][namespace]:\tmessage
     */
    public function testMessageIsFormattedWithLevelAndNamespace(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory('test-ns', [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger();

        $logger->info('my message');

        $this->assertStringContainsString('[INFO]', $spy->records[0]['message']);
        $this->assertStringContainsString('[test-ns]', $spy->records[0]['message']);
        $this->assertStringContainsString('my message', $spy->records[0]['message']);
    }

    /**
     * @testdox createLogger() args cannot override the target logger — constructor-registered loggers are always used
     */
    public function testCreateLoggerArgsCannotOverrideLogger(): void
    {
        $spy = new InMemoryLogger();
        // Construct with spy at DEBUG; pass NullLogger via args — 'logger' is stripped, spy is still used.
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger([
            'logger'   => new NullLogger(),
            'logLevel' => LogLevel::ERROR,
        ]);

        $logger->debug('test');

        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox createLogger() args cannot override the log level threshold — per-logger constructor logLevel is used
     */
    public function testCreateLoggerArgsCannotOverrideLogLevel(): void
    {
        $spy = new InMemoryLogger();
        // Constructor sets logger=$spy at ERROR; args try to lower to DEBUG — 'logLevel' arg is stripped.
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => LogLevel::ERROR]]))->createLogger([
            'logLevel' => LogLevel::DEBUG,
        ]);

        $logger->debug('test');
        $this->assertCount(0, $spy->records);

        $logger->error('test');
        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox namespace arg passed to createLogger() extends the breadcrumb path for the next logger level
     */
    public function testNamespaceArgExtendsChildBreadcrumbPath(): void
    {
        $spy = new InMemoryLogger();
        $factory = new LoggerFactory('App', [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]);

        // namespace='Handler' is recorded by the child factory; the grandchild logger uses the full path.
        $grandchild = $factory->createLogger()
                              ->createLogger(['namespace' => 'Handler'])
                              ->createLogger()
                              ->createLogger(['namespace' => 'HandlerTwo']);

        $grandchild->debug('msg');

        $this->assertStringContainsString('App/Handler/HandlerTwo', $spy->records[0]['message']);
    }

    /**
     * @testdox createLogger() on the returned logger delegates back to the original factory
     */
    public function testCreateLoggerOnReturnedLoggerDelegatesToFactory(): void
    {
        $factory = new LoggerFactory();
        $logger  = $factory->createLogger();
        $child   = $logger->createLogger();

        $this->assertInstanceOf(LoggerInterface::class, $child);
        $this->assertInstanceOf(LoggerFactoryInterface::class, $child);
    }

    /**
     * @testdox context array is passed through to the underlying logger unchanged
     */
    public function testContextIsPassedThrough(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy]]))->createLogger();

        $logger->error('msg', ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $spy->records[0]['context']);
    }

    /**
     * @testdox an unknown logLevel falls back to the ERROR threshold
     */
    public function testUnknownLogLevelFallsBackToError(): void
    {
        $spy = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => 'unknown-level']]))->createLogger();

        $logger->error('error passes');
        $logger->warning('warning suppressed');

        $this->assertCount(1, $spy->records);
    }
}
