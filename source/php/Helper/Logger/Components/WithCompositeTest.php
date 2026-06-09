<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithCompositeTest extends TestCase
{
    /**
     * @testdox log() calls log() on every injected logger
     */
    public function testLogDelegatesToAllLoggers(): void
    {
        $a = new InMemoryLogger();
        $b = new InMemoryLogger();
        $logger = new WithComposite([$a, $b]);

        $logger->log(LogLevel::INFO, 'hello');

        $this->assertCount(1, $a->records);
        $this->assertCount(1, $b->records);
    }

    /**
     * @testdox log() passes the same level to every logger
     */
    public function testLogPassesSameLevelToAllLoggers(): void
    {
        $a = new InMemoryLogger();
        $b = new InMemoryLogger();
        $logger = new WithComposite([$a, $b]);

        $logger->log(LogLevel::ERROR, 'boom');

        $this->assertSame(LogLevel::ERROR, $a->records[0]['level']);
        $this->assertSame(LogLevel::ERROR, $b->records[0]['level']);
    }

    /**
     * @testdox log() passes the same message to every logger
     */
    public function testLogPassesSameMessageToAllLoggers(): void
    {
        $a = new InMemoryLogger();
        $b = new InMemoryLogger();
        $logger = new WithComposite([$a, $b]);

        $logger->log(LogLevel::WARNING, 'watch out');

        $this->assertSame('watch out', $a->records[0]['message']);
        $this->assertSame('watch out', $b->records[0]['message']);
    }

    /**
     * @testdox log() passes the same context to every logger
     */
    public function testLogPassesSameContextToAllLoggers(): void
    {
        $a = new InMemoryLogger();
        $b = new InMemoryLogger();
        $logger = new WithComposite([$a, $b]);

        $logger->log(LogLevel::DEBUG, 'msg', ['key' => 'value']);

        $this->assertSame(['key' => 'value'], $a->records[0]['context']);
        $this->assertSame(['key' => 'value'], $b->records[0]['context']);
    }

    /**
     * @testdox log() with an empty logger list does nothing
     */
    public function testLogWithNoLoggersDoesNothing(): void
    {
        $logger = new WithComposite([]);

        $logger->log(LogLevel::INFO, 'silent');

        $this->addToAssertionCount(1);
    }

    /**
     * @testdox log() records multiple calls in the correct order on each logger
     */
    public function testLogRecordsMultipleCallsInOrder(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithComposite([$spy]);

        $logger->log(LogLevel::INFO, 'first');
        $logger->log(LogLevel::ERROR, 'second');

        $this->assertSame('first', $spy->records[0]['message']);
        $this->assertSame('second', $spy->records[1]['message']);
    }
}
