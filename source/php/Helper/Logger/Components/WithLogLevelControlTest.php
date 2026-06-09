<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithLogLevelControlTest extends TestCase
{
    /**
     * @testdox messages at or above the configured log level are forwarded to the inner logger
     */
    public function testForwardsMessagesAtOrAboveConfiguredLevel(): void
    {
        $spy = new InMemoryLogger();
        // Default logLevel is 500 (ERROR)
        $logger = new WithLogLevelControl($spy);

        $logger->log(LogLevel::ERROR, 'error message');
        $logger->log(LogLevel::CRITICAL, 'critical message');
        $logger->log(LogLevel::ALERT, 'alert message');
        $logger->log(LogLevel::EMERGENCY, 'emergency message');

        $this->assertCount(4, $spy->records);
    }

    /**
     * @testdox messages below the configured log level are suppressed
     */
    public function testSuppressesMessagesBelowConfiguredLevel(): void
    {
        $spy = new InMemoryLogger();
        // Default logLevel is 500 (ERROR)
        $logger = new WithLogLevelControl($spy);

        $logger->log(LogLevel::WARNING, 'warning message');
        $logger->log(LogLevel::NOTICE, 'notice message');
        $logger->log(LogLevel::INFO, 'info message');
        $logger->log(LogLevel::DEBUG, 'debug message');

        $this->assertCount(0, $spy->records);
    }

    /**
     * @testdox a message exactly at the threshold is forwarded
     */
    public function testMessageAtThresholdIsForwarded(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 400); // WARNING = 400

        $logger->log(LogLevel::WARNING, 'at threshold');

        $this->assertCount(1, $spy->records);
        $this->assertSame('at threshold', $spy->records[0]['message']);
    }

    /**
     * @testdox a message one step below the threshold is suppressed
     */
    public function testMessageJustBelowThresholdIsSuppressed(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 400); // WARNING = 400

        $logger->log(LogLevel::NOTICE, 'just below threshold'); // NOTICE = 300

        $this->assertCount(0, $spy->records);
    }

    /**
     * @testdox log() forwards level, message, and context unchanged
     */
    public function testForwardsLevelMessageAndContext(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 100); // DEBUG = 100, passes everything

        $logger->log(LogLevel::INFO, 'hello', ['key' => 'value']);

        $this->assertSame(LogLevel::INFO, $spy->records[0]['level']);
        $this->assertSame('hello', $spy->records[0]['message']);
        $this->assertSame(['key' => 'value'], $spy->records[0]['context']);
    }

    /**
     * @testdox when logLevel is set to DEBUG all PSR-3 levels are forwarded
     */
    public function testAllLevelsPassWhenThresholdIsDebug(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 100); // DEBUG = 100

        foreach ([
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ] as $level) {
            $logger->log($level, $level);
        }

        $this->assertCount(8, $spy->records);
    }

    /**
     * @testdox when logLevel is set to EMERGENCY only emergency messages are forwarded
     */
    public function testOnlyEmergencyPassesWhenThresholdIsEmergency(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 800); // EMERGENCY = 800

        foreach ([
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
        ] as $level) {
            $logger->log($level, $level);
        }

        $this->assertCount(0, $spy->records);

        $logger->log(LogLevel::EMERGENCY, 'emergency');
        $this->assertCount(1, $spy->records);
    }
}
