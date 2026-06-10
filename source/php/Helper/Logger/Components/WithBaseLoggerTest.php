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

        $logger->info('test');
        $this->addToAssertionCount(1);
    }

    /**
     * @testdox log() delegates level, message, and context to the injected logger
     */
    public function testLogDelegatesToInjectedLogger(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->log(LogLevel::ERROR, 'something went wrong', ['key' => 'value']);

        $this->assertCount(1, $spy->records);
        $this->assertSame(LogLevel::ERROR,          $spy->records[0]['level']);
        $this->assertSame('something went wrong',   $spy->records[0]['message']);
        $this->assertSame(['key' => 'value'],        $spy->records[0]['context']);
    }

    /**
     * @testdox each PSR-3 convenience method calls log() with the correct level
     */
    public static function levelMethodProvider(): array
    {
        return [
            'emergency' => [LogLevel::EMERGENCY, 'emergency'],
            'alert'     => [LogLevel::ALERT,     'alert'],
            'critical'  => [LogLevel::CRITICAL,  'critical'],
            'error'     => [LogLevel::ERROR,     'error'],
            'warning'   => [LogLevel::WARNING,   'warning'],
            'notice'    => [LogLevel::NOTICE,    'notice'],
            'info'      => [LogLevel::INFO,      'info'],
            'debug'     => [LogLevel::DEBUG,     'debug'],
        ];
    }

    /**
     * @dataProvider levelMethodProvider
     */
    public function testLevelMethodDelegatesToLog(string $level, string $method): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithBaseLogger($spy);

        $logger->$method('msg');

        $this->assertSame($level, $spy->records[0]['level']);
    }

    /**
     * @testdox createLogger() returns an object implementing both LoggerInterface and LoggerFactoryInterface
     */
    public function testCreateLoggerReturnsLoggerAndFactoryInterface(): void
    {
        $logger = new WithBaseLogger();

        $child = $logger->createLogger();

        $this->assertInstanceOf(LoggerInterface::class, $child);
        $this->assertInstanceOf(LoggerFactoryInterface::class, $child);
    }

    /**
     * @testdox createLogger() delegates to the injected factory and passes args through
     */
    public function testCreateLoggerPassesArgsToFactory(): void
    {
        $spy    = $this->createSpyFactory();
        $logger = new WithBaseLogger(new NullLogger(), $spy);

        $logger->createLogger(['namespace' => 'custom']);

        $this->assertSame([['namespace' => 'custom']], $spy->calls);
    }
}
