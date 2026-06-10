<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithLogLevelControlTest extends TestCase
{
    /**
     * @testdox default threshold (ERROR) forwards ERROR and suppresses WARNING
     */
    public function testDefaultThreshold(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy); // ERROR = 500

        $logger->log(LogLevel::ERROR,   'passes');
        $logger->log(LogLevel::WARNING, 'suppressed');

        $this->assertCount(1, $spy->records);
    }

    /**
     * @testdox a message exactly at the threshold is forwarded; one step below is suppressed
     */
    public function testBoundaryAtCustomThreshold(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 400); // WARNING = 400

        $logger->log(LogLevel::WARNING, 'at threshold');
        $logger->log(LogLevel::NOTICE,  'below threshold');

        $this->assertCount(1, $spy->records);
        $this->assertSame('at threshold', $spy->records[0]['message']);
    }

    /**
     * @testdox log() forwards level, message, and context unchanged
     */
    public function testForwardsLevelMessageAndContext(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 100); // DEBUG = 100, passes everything

        $logger->log(LogLevel::INFO, 'hello', ['key' => 'value']);

        $this->assertSame(LogLevel::INFO,    $spy->records[0]['level']);
        $this->assertSame('hello',           $spy->records[0]['message']);
        $this->assertSame(['key' => 'value'], $spy->records[0]['context']);
    }

    /**
     * @testdox when threshold is EMERGENCY only emergency messages are forwarded
     */
    public function testOnlyEmergencyPassesWhenThresholdIsEmergency(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithLogLevelControl($spy, 800); // EMERGENCY = 800

        $logger->log(LogLevel::ALERT,     'below');
        $logger->log(LogLevel::EMERGENCY, 'passes');

        $this->assertCount(1, $spy->records);
    }
}
