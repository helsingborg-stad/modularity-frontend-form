<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithFormatterTest extends TestCase
{
    /**
     * @testdox log() formats message as [LEVEL][namespace]:\t{message}
     */
    public function testLogFormatsMessageWithLevelAndNamespace(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'MyNamespace');

        $logger->log(LogLevel::INFO, 'something happened');

        $this->assertSame("[INFO][MyNamespace]:\tsomething happened", $spy->records[0]['message']);
    }

    /**
     * @testdox log() uppercases the level in the formatted message
     */
    public function testLogUppercasesLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::ERROR, 'msg');

        $this->assertStringContainsString('[ERROR]', $spy->records[0]['message']);
    }

    /**
     * @testdox log() preserves the original (lowercase) level passed to the inner logger
     */
    public function testLogPreservesOriginalLevel(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::WARNING, 'msg');

        $this->assertSame(LogLevel::WARNING, $spy->records[0]['level']);
    }

    /**
     * @testdox log() passes context array through to the inner logger unchanged
     */
    public function testLogPassesContextUnchanged(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::DEBUG, 'msg', ['foo' => 'bar', 'baz' => 42]);

        $this->assertSame(['foo' => 'bar', 'baz' => 42], $spy->records[0]['context']);
    }

    /**
     * @testdox log() includes the namespace in the formatted message
     */
    public function testLogIncludesNamespaceInMessage(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'FormHandler');

        $logger->log(LogLevel::NOTICE, 'msg');

        $this->assertStringContainsString('[FormHandler]', $spy->records[0]['message']);
    }

    /**
     * @testdox log() works with a Stringable message
     */
    public function testLogAcceptsStringable(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $stringable = new class implements \Stringable {
            public function __toString(): string { return 'stringable message'; }
        };

        $logger->log(LogLevel::INFO, $stringable);

        $this->assertStringContainsString('stringable message', $spy->records[0]['message']);
    }
}
