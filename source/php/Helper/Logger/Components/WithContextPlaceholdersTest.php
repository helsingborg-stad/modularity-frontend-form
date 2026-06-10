<?php

namespace ModularityFrontendForm\Helper\Logger\Components;

use ModularityFrontendForm\Helper\Logger\Loggers\InMemoryLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class WithContextPlaceholdersTest extends TestCase
{
    // ── Basic interpolation ───────────────────────────────────────────────────

    /**
     * @testdox log() replaces a single {placeholder} with its context value
     */
    public function testReplaceSinglePlaceholder(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, 'Hello {name}', ['name' => 'World']);

        $this->assertSame('Hello World', $spy->records[0]['message']);
    }

    /**
     * @testdox log() replaces multiple placeholders in a single message
     */
    public function testReplaceMultiplePlaceholders(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{greeting} {name}!', ['greeting' => 'Hello', 'name' => 'World']);

        $this->assertSame('Hello World!', $spy->records[0]['message']);
    }

    /**
     * @testdox log() passes message unchanged when there are no placeholders
     */
    public function testNoPlaceholdersPassesThroughUnchanged(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, 'plain message', ['key' => 'value']);

        $this->assertSame('plain message', $spy->records[0]['message']);
    }

    /**
     * @testdox log() leaves {placeholder} unreplaced when the key is absent from context
     */
    public function testMissingContextKeyLeavesPlaceholderIntact(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, 'Hello {name}', []);

        $this->assertSame('Hello {name}', $spy->records[0]['message']);
    }

    // ── Delimiter / whitespace rules (PSR-3) ──────────────────────────────────

    /**
     * @testdox log() does NOT interpolate when there is whitespace inside the braces
     */
    public function testWhitespaceInsideBracesIsNotInterpolated(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, 'Hello { name }', ['name' => 'World']);

        $this->assertSame('Hello { name }', $spy->records[0]['message']);
    }

    // ── Valid placeholder name characters (PSR-3 SHOULD) ─────────────────────

    /**
     * @testdox log() interpolates placeholder names composed of A-Z, a-z, 0-9, and underscore
     */
    public function testValidAlphanumericAndUnderscoreKey(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{User_ID_42}', ['User_ID_42' => 'abc']);

        $this->assertSame('abc', $spy->records[0]['message']);
    }

    /**
     * @testdox log() does NOT interpolate placeholder names that contain invalid characters such as a hyphen
     */
    public function testInvalidCharacterInPlaceholderNameIsNotInterpolated(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{user-name}', ['user-name' => 'Alice']);

        $this->assertSame('{user-name}', $spy->records[0]['message']);
    }

    // ── Context and level pass-through ────────────────────────────────────────

    /**
     * @testdox log() forwards the original level and context to the inner logger unchanged
     */
    public function testLevelAndContextPassedToInnerLoggerUnchanged(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::WARNING, '{key}', ['key' => 'val', 'extra' => 42]);

        $this->assertSame(LogLevel::WARNING,              $spy->records[0]['level']);
        $this->assertSame(['key' => 'val', 'extra' => 42], $spy->records[0]['context']);
    }

    // ── Non-string context values ─────────────────────────────────────────────

    public static function scalarContextValueProvider(): array
    {
        return [
            'int'   => [42,   '42'],
            'float' => [3.14, '3.14'],
        ];
    }

    /**
     * @testdox log() casts scalar (int/float) context values to string when interpolating
     * @dataProvider scalarContextValueProvider
     */
    public function testScalarContextValueIsCastToString(int|float $value, string $expected): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{val}', ['val' => $value]);

        $this->assertSame($expected, $spy->records[0]['message']);
    }

    public static function jsonContextValueProvider(): array
    {
        $obj       = new \stdClass();
        $obj->name = 'Alice';
        return [
            'array'  => [['foo', 'bar'], json_encode(['foo', 'bar'], JSON_PRETTY_PRINT)],
            'object' => [$obj,           json_encode($obj,           JSON_PRETTY_PRINT)],
        ];
    }

    /**
     * @testdox log() JSON pretty-prints array and non-Stringable object context values when interpolating
     * @dataProvider jsonContextValueProvider
     */
    public function testComplexContextValueIsJsonEncoded(array|object $value, string $expected): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{val}', ['val' => $value]);

        $this->assertSame($expected, $spy->records[0]['message']);
    }

    /**
     * @testdox log() calls __toString() on a Stringable context value when interpolating
     */
    public function testStringableContextValue(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $stringable = new class implements \Stringable {
            public function __toString(): string { return 'stringified'; }
        };

        $logger->log(LogLevel::INFO, 'Value: {val}', ['val' => $stringable]);

        $this->assertSame('Value: stringified', $spy->records[0]['message']);
    }

    /**
     * @testdox log() accepts a Stringable message and interpolates placeholders in its string value
     */
    public function testStringableMessage(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $message = new class implements \Stringable {
            public function __toString(): string { return 'Hello {name}'; }
        };

        $logger->log(LogLevel::INFO, $message, ['name' => 'World']);

        $this->assertSame('Hello World', $spy->records[0]['message']);
    }

    // ── Dot notation for nested context ───────────────────────────────────────

    /**
     * @testdox log() resolves {parent.child} to context['parent']['child'] using dot notation
     */
    public function testDotNotationResolvesOneLevel(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, 'Hello {user.name}', ['user' => ['name' => 'Alice']]);

        $this->assertSame('Hello Alice', $spy->records[0]['message']);
    }

    /**
     * @testdox log() resolves deeply nested {a.b.c} placeholder using dot notation
     */
    public function testDotNotationResolvesMultipleLevels(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{a.b.c}', ['a' => ['b' => ['c' => 'deep']]]);

        $this->assertSame('deep', $spy->records[0]['message']);
    }

    /**
     * @testdox log() leaves {parent.child} unreplaced when the nested key does not exist
     */
    public function testDotNotationMissingLeafKeyLeavesPlaceholderIntact(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{user.email}', ['user' => ['name' => 'Alice']]);

        $this->assertSame('{user.email}', $spy->records[0]['message']);
    }

    /**
     * @testdox log() leaves {parent.child} unreplaced when the parent key does not exist in context
     */
    public function testDotNotationMissingParentKeyLeavesPlaceholderIntact(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{missing.key}', []);

        $this->assertSame('{missing.key}', $spy->records[0]['message']);
    }

    /**
     * @testdox log() prefers a flat context key over dot-notation traversal when both could match
     */
    public function testFlatKeyTakesPrecedenceOverDotNotation(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy);

        $logger->log(LogLevel::INFO, '{user.name}', [
            'user.name' => 'flat',
            'user'      => ['name' => 'nested'],
        ]);

        $this->assertSame('flat', $spy->records[0]['message']);
    }

    // ── Custom resolvers ──────────────────────────────────────────────────────

    /**
     * @testdox custom resolver can transform a type that is otherwise left as a placeholder
     */
    public function testCustomResolverTransformsArray(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy, [
            ['is_array', fn($v) => implode(',', $v)],
        ]);

        $logger->log(LogLevel::INFO, 'Tags: {tags}', ['tags' => ['php', 'psr', 'log']]);

        $this->assertSame('Tags: php,psr,log', $spy->records[0]['message']);
    }

    /**
     * @testdox the first matching resolver wins; later resolvers are not evaluated
     */
    public function testFirstValidResolverWins(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy, [
            ['is_string', fn($_) => 'first'],
            ['is_string', fn($_) => 'second'],
        ]);

        $logger->log(LogLevel::INFO, '{val}', ['val' => 'anything']);

        $this->assertSame('first', $spy->records[0]['message']);
    }

    /**
     * @testdox no matching resolver leaves the placeholder intact
     */
    public function testNoMatchingResolverLeavesPlaceholderIntact(): void
    {
        $spy    = new InMemoryLogger();
        $logger = new WithContextPlaceholders($spy, [
            ['is_int', fn($v) => (string) $v],
        ]);

        $logger->log(LogLevel::INFO, '{val}', ['val' => 'a string']);

        $this->assertSame('{val}', $spy->records[0]['message']);
    }
}
