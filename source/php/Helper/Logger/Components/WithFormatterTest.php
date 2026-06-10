<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithFormatterTest extends TestCase
{
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

    // ── namespace: string vs array ────────────────────────────────────────────

    /**
     * @testdox string namespace is passed through to the formatted message as-is
     */
    public function testStringNamespacePassedThrough(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'MyService');

        $logger->log(LogLevel::INFO, 'msg');

        $this->assertStringContainsString('[MyService]', $spy->records[0]['message']);
    }

    /**
     * @testdox array namespace is joined with '/' in the formatted message
     */
    public function testArrayNamespaceJoinedWithSlash(): void
    {
        $spy = new InMemoryLogger();
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
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns');

        $logger->log(LogLevel::ERROR, 'hello');

        $this->assertSame('[ERROR]:[ns]: hello', $spy->records[0]['message']);
    }

    /**
     * @testdox custom formatStr is used instead of the default template
     */
    public function testCustomFormatStrIsApplied(): void
    {
        $spy = new InMemoryLogger();
        $logger = new WithFormatter($spy, 'ns', formatStr: '%2$s|%1$s|%3$s');

        $logger->log(LogLevel::INFO, 'hello');

        $this->assertSame('ns|INFO|hello', $spy->records[0]['message']);
    }

    // ── breadcrumbDirection ───────────────────────────────────────────────────

    /**
     * @testdox direction='left' keeps the last element when maxCount=1
     */
    public function testBreadcrumbLeftMaxCountOneKeepsLastElement(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $result = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'left', 1);

        $this->assertSame(['last'], $result);
    }

    /**
     * @testdox direction='right' keeps the first element when maxCount=1
     */
    public function testBreadcrumbRightMaxCountOneKeepsFirstElement(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $result = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'right', 1);

        $this->assertSame(['first'], $result);
    }

    /**
     * @testdox an invalid direction string falls back to right behaviour
     */
    public function testBreadcrumbInvalidDirectionFallsBackToRight(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $left  = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'left',    1);
        $right = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'right',   1);
        $bad   = $formatter->breadcrumbPaths(['first', 'middle', 'last'], 'invalid', 1);

        $this->assertNotSame($left, $bad, 'invalid direction should not behave like left');
        $this->assertSame($right, $bad,   'invalid direction should behave like right');
    }

    /**
     * @testdox direction='left' keeps rightmost middle elements when trimming
     */
    public function testBreadcrumbLeftKeepsRightmostMiddle(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        // ['a','b','c','d','e'], maxCount=3 → first + rightmost-middle + last
        $result = $formatter->breadcrumbPaths(['a', 'b', 'c', 'd', 'e'], 'left', 3);

        $this->assertSame(['a', 'd', 'e'], $result);
    }

    /**
     * @testdox direction='right' keeps leftmost middle elements when trimming
     */
    public function testBreadcrumbRightKeepsLeftmostMiddle(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        // ['a','b','c','d','e'], maxCount=3 → first + leftmost-middle + last
        $result = $formatter->breadcrumbPaths(['a', 'b', 'c', 'd', 'e'], 'right', 3);

        $this->assertSame(['a', 'b', 'e'], $result);
    }

    // ── breadcrumbMaxCount combined with breadcrumbDirection ──────────────────

    /**
     * @testdox breadcrumbMaxCount=2 always returns first and last elements regardless of direction
     */
    public function testMaxCountTwoReturnsFirstAndLast(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $this->assertSame(['a', 'd'], $formatter->breadcrumbPaths(['a', 'b', 'c', 'd'], 'left',  2));
        $this->assertSame(['a', 'd'], $formatter->breadcrumbPaths(['a', 'b', 'c', 'd'], 'right', 2));
    }

    /**
     * @testdox breadcrumbMaxCount=4 with direction='left' keeps the two rightmost middle elements
     */
    public function testMaxCountFourLeftKeepsTwoRightmostMiddleElements(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        // ['a','b','c','d','e'], middle=['b','c','d'], rightmost 2 of middle = ['c','d']
        $result = $formatter->breadcrumbPaths(['a', 'b', 'c', 'd', 'e'], 'left', 4);

        $this->assertSame(['a', 'c', 'd', 'e'], $result);
    }

    /**
     * @testdox breadcrumbMaxCount=4 with direction='right' keeps the two leftmost middle elements
     */
    public function testMaxCountFourRightKeepsTwoLeftmostMiddleElements(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        // ['a','b','c','d','e'], middle=['b','c','d'], leftmost 2 of middle = ['b','c']
        $result = $formatter->breadcrumbPaths(['a', 'b', 'c', 'd', 'e'], 'right', 4);

        $this->assertSame(['a', 'b', 'c', 'e'], $result);
    }

    /**
     * @testdox breadcrumbMaxCount larger than path count returns all elements
     */
    public function testMaxCountLargerThanPathCountReturnsAll(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $result = $formatter->breadcrumbPaths(['a', 'b', 'c'], 'left', 10);

        $this->assertSame(['a', 'b', 'c'], $result);
    }

    /**
     * @testdox breadcrumbMaxCount<=0 is clamped to 1 and direction='left' returns the last element
     */
    public function testMaxCountZeroClampedToOneLeft(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $result = $formatter->breadcrumbPaths(['first', 'last'], 'left', 0);

        $this->assertSame(['last'], $result);
    }

    /**
     * @testdox empty paths array returns an empty array regardless of direction or maxCount
     */
    public function testEmptyPathsReturnsEmptyArray(): void
    {
        $formatter = new WithFormatter(new \Psr\Log\NullLogger(), 'ns');

        $this->assertSame([], $formatter->breadcrumbPaths([], 'left',  3));
        $this->assertSame([], $formatter->breadcrumbPaths([], 'right', 3));
    }

    /**
     * @testdox breadcrumbDirection and breadcrumbMaxCount are applied when array namespace is logged
     */
    public function testArrayNamespaceUsesDirectionAndMaxCount(): void
    {
        $spy = new InMemoryLogger();
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
