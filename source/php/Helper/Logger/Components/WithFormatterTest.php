<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class WithFormatterTest extends TestCase
{
    /**
     * @testdox log() forwards the original level and context to the inner logger unchanged
     */
    public function testLogForwardsLevelAndContext(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::WARNING, 'msg', ['foo' => 'bar']);

        $this->assertSame(LogLevel::WARNING, $spy->records[0]['level']);
        $this->assertSame(['foo' => 'bar'],  $spy->records[0]['context']);
    }

    /**
     * @testdox log() works with a Stringable message
     */
    public function testLogAcceptsStringable(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $stringable = new class implements \Stringable {
            public function __toString(): string { return 'stringable message'; }
        };

        $logger->log(LogLevel::INFO, $stringable);

        $this->assertStringContainsString('stringable message', $spy->records[0]['message']);
    }

    // ── namespace: string vs array ────────────────────────────────────────────

    /**
     * @testdox string namespace is passed through to the formatted message as-is
     */
    public function testStringNamespacePassedThrough(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'MyService');

        $logger->log(LogLevel::INFO, 'msg');

        $this->assertStringContainsString('[MyService]', $spy->records[0]['message']);
    }

    /**
     * @testdox array namespace is joined with '/' in the formatted message
     */
    public function testArrayNamespaceJoinedWithSlash(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, ['App', 'Handler', 'Form'], breadcrumbMaxCount: 10);

        $logger->log(LogLevel::INFO, 'msg');

        $this->assertStringContainsString('App/Handler/Form', $spy->records[0]['message']);
    }

    // ── formatStr ─────────────────────────────────────────────────────────────

    /**
     * @testdox null formatStr uses the default [LEVEL]:[NS]: message template
     */
    public function testNullFormatStrUsesDefault(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::ERROR, 'hello');

        $this->assertSame('[ERROR]:[ns]: hello', $spy->records[0]['message']);
    }

    /**
     * @testdox custom formatStr is used instead of the default template
     */
    public function testCustomFormatStrIsApplied(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns', formatStr: '%2$s|%1$s|%3$s');

        $logger->log(LogLevel::INFO, 'hello');

        $this->assertSame('ns|INFO|hello', $spy->records[0]['message']);
    }

    // ── breadcrumbPaths ───────────────────────────────────────────────────────

    public static function breadcrumbPathsProvider(): array
    {
        return [
            // maxCount=1: left picks last, right picks first
            'left  maxCount=1' => [['first', 'middle', 'last'], 'left',  1, ['last']],
            'right maxCount=1' => [['first', 'middle', 'last'], 'right', 1, ['first']],
            // maxCount=2: always first + last regardless of direction
            'left  maxCount=2' => [['a', 'b', 'c', 'd'], 'left',  2, ['a', 'd']],
            'right maxCount=2' => [['a', 'b', 'c', 'd'], 'right', 2, ['a', 'd']],
            // maxCount=3: one middle element; left keeps rightmost, right keeps leftmost
            'left  maxCount=3' => [['a', 'b', 'c', 'd', 'e'], 'left',  3, ['a', 'd', 'e']],
            'right maxCount=3' => [['a', 'b', 'c', 'd', 'e'], 'right', 3, ['a', 'b', 'e']],
            // maxCount=4: two middle elements; left keeps rightmost two, right keeps leftmost two
            'left  maxCount=4' => [['a', 'b', 'c', 'd', 'e'], 'left',  4, ['a', 'c', 'd', 'e']],
            'right maxCount=4' => [['a', 'b', 'c', 'd', 'e'], 'right', 4, ['a', 'b', 'c', 'e']],
        ];
    }

    /**
     * @testdox breadcrumbPaths() trims the path correctly for a given direction and maxCount
     * @dataProvider breadcrumbPathsProvider
     */
    public function testBreadcrumbPaths(array $paths, string $direction, int $maxCount, array $expected): void
    {
        $formatter = new WithFormatter(new NullLogger(), 'ns');

        $this->assertSame($expected, $formatter->breadcrumbPaths($paths, $direction, $maxCount));
    }

    /**
     * @testdox an invalid direction string falls back to right behaviour
     */
    public function testBreadcrumbInvalidDirectionFallsBackToRight(): void
    {
        $formatter = new WithFormatter(new NullLogger(), 'ns');

        $left  = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'left',    1);
        $right = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'right',   1);
        $bad   = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'invalid', 1);

        $this->assertNotSame($left,  $bad, 'invalid direction should not behave like left');
        $this->assertSame($right, $bad,    'invalid direction should behave like right');
    }

    /**
     * @testdox breadcrumbMaxCount larger than path count returns all elements
     */
    public function testMaxCountLargerThanPathCountReturnsAll(): void
    {
        $formatter = new WithFormatter(new NullLogger(), 'ns');

        $this->assertSame(['a', 'b', 'c'], $formatter->breadcrumbPaths(['a', 'b', 'c'], 'left', 10));
    }

    /**
     * @testdox breadcrumbMaxCount<=0 is clamped to 1 and direction='left' returns the last element
     */
    public function testMaxCountZeroClampedToOne(): void
    {
        $formatter = new WithFormatter(new NullLogger(), 'ns');

        $this->assertSame(['last'], $formatter->breadcrumbPaths(['first', 'last'], 'left', 0));
    }

    /**
     * @testdox empty paths array returns an empty array regardless of direction or maxCount
     */
    public function testEmptyPathsReturnsEmptyArray(): void
    {
        $formatter = new WithFormatter(new NullLogger(), 'ns');

        $this->assertSame([], $formatter->breadcrumbPaths([], 'left',  3));
        $this->assertSame([], $formatter->breadcrumbPaths([], 'right', 3));
    }

    /**
     * @testdox breadcrumbDirection and breadcrumbMaxCount are applied when array namespace is logged
     */
    public function testArrayNamespaceUsesDirectionAndMaxCount(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithFormatter(
            $spy,
            ['a', 'b', 'c', 'd', 'e'],
            breadcrumbDirection: 'right',
            breadcrumbMaxCount: 3
        );

        $logger->log(LogLevel::INFO, 'msg');

        $this->assertStringContainsString('[a/b/e]', $spy->records[0]['message']);
    }
}
