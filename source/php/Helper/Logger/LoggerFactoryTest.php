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
     * @testdox default log level is ERROR — messages at ERROR pass, below are suppressed
     */
    public function testDefaultLogLevel(): void
    {
        $spy    = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy]]))->createLogger();

        $logger->error('passes');
        $logger->warning('suppressed');

        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox custom logLevel registered via constructor is respected
     */
    public function testCustomLogLevelIsRespected(): void
    {
        $spy    = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger();

        $logger->debug('test');

        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox message is formatted as [LEVEL][namespace]:\tmessage
     */
    public function testMessageIsFormattedWithLevelAndNamespace(): void
    {
        $spy    = new InMemoryLogger();
        $logger = (new LoggerFactory('test-ns', [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger();

        $logger->info('my message');

        $this->assertStringContainsString('[INFO]', $spy->records[0]['message']);
        $this->assertStringContainsString('[test-ns]', $spy->records[0]['message']);
        $this->assertStringContainsString('my message', $spy->records[0]['message']);
    }

    /**
     * @testdox createLogger() args cannot override constructor-registered logger or logLevel
     */
    public function testCreateLoggerArgsCannotOverrideConstructorConfig(): void
    {
        $spy    = new InMemoryLogger();
        // Spy at DEBUG; args try to swap in NullLogger and raise threshold to ERROR — both are stripped.
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => LogLevel::DEBUG]]))->createLogger([
            'logger'   => new NullLogger(),
            'logLevel' => LogLevel::ERROR,
        ]);

        $logger->debug('test');

        $this->assertCount(1, $spy->records);
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
        $spy    = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy]]))->createLogger();

        $logger->error('msg', ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $spy->records[0]['context']);
    }

    /**
     * @testdox an unknown logLevel falls back to the ERROR threshold
     */
    public function testUnknownLogLevelFallsBackToError(): void
    {
        $spy    = new InMemoryLogger();
        $logger = (new LoggerFactory(loggers: [['logger' => $spy, 'logLevel' => 'unknown-level']]))->createLogger();

        $logger->error('error passes');
        $logger->warning('warning suppressed');

        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox branching namespace tree: App → App/ModuleA → App/ModuleA/ComponentA, then back to App → App/ModuleB → App/ModuleB/ComponentB
     */
    public function testBranchingNamespaceTree(): void
    {
        $spy     = new InMemoryLogger();
        $factory = new LoggerFactory('App', [['logger' => $spy, 'logLevel' => LogLevel::DEBUG, 'breadcrumbMaxCount' => -1]]);

        $app = $factory->createLogger();

        $moduleA    = $app->createLogger(['namespace' => 'ModuleA'])->createLogger();
        $componentA = $moduleA->createLogger(['namespace' => 'ComponentA'])->createLogger();

        $moduleB    = $app->createLogger(['namespace' => 'ModuleB'])->createLogger();
        $componentB = $moduleB->createLogger(['namespace' => 'ComponentB'])->createLogger();

        $app->debug('app');
        $moduleA->debug('moduleA');
        $componentA->debug('componentA');
        $moduleB->debug('moduleB');
        $componentB->debug('componentB');

        $this->assertStringContainsString('[App]',                    $spy->records[0]['message']);
        $this->assertStringContainsString('[App/ModuleA]',            $spy->records[1]['message']);
        $this->assertStringContainsString('[App/ModuleA/ComponentA]', $spy->records[2]['message']);
        $this->assertStringContainsString('[App/ModuleB]',            $spy->records[3]['message']);
        $this->assertStringContainsString('[App/ModuleB/ComponentB]', $spy->records[4]['message']);
    }
}
