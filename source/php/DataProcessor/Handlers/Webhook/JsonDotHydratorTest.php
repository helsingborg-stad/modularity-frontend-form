<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use PHPUnit\Framework\TestCase;

class JsonDotHydratorTest extends TestCase
{
    private JsonDotHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new JsonDotHydrator();
    }

    /** @testdox Returns template unchanged when it is not valid JSON */
    public function testInvalidJsonReturnsTemplateUnchanged(): void
    {
        static::assertSame('not json', $this->hydrator->hydrate('not json', []));
    }

    /** @testdox Returns empty array when template is an empty object */
    public function testEmptyObjectReturnsEmptyArray(): void
    {
        static::assertSame('[]', $this->hydrator->hydrate('{}', ['x' => 1]));
    }

    /** @testdox Returns template unchanged when it contains no placeholders */
    public function testTemplateWithNoPlaceholdersIsReturnedUnchanged(): void
    {
        static::assertSame(
            '{"name":"Alice"}',
            $this->hydrator->hydrate('{"name":"Alice"}', [])
        );
    }

    /** @testdox Replaces a full-value placeholder with a string value */
    public function testFullValueStringReplacement(): void
    {
        static::assertSame(
            '{"name":"Alice"}',
            $this->hydrator->hydrate('{"name":"{{ name }}"}', ['name' => 'Alice'])
        );
    }

    /** @testdox Preserves integer type for a full-value replacement */
    public function testFullValueIntegerPreservesType(): void
    {
        static::assertSame(
            '{"count":42}',
            $this->hydrator->hydrate('{"count":"{{ count }}"}', ['count' => 42])
        );
    }

    /** @testdox Preserves boolean type for a full-value replacement */
    public function testFullValueBooleanPreservesType(): void
    {
        static::assertSame(
            '{"active":true}',
            $this->hydrator->hydrate('{"active":"{{ active }}"}', ['active' => true])
        );
    }

    /** @testdox Replaces a full-value placeholder with an array value */
    public function testFullValueArrayReplacement(): void
    {
        static::assertSame(
            '{"items":["a","b"]}',
            $this->hydrator->hydrate('{"items":"{{ items }}"}', ['items' => ['a', 'b']])
        );
    }

    /** @testdox Resolves a dot-notation path to a nested value */
    public function testDotNotationResolvesNestedValue(): void
    {
        static::assertSame(
            '{"city":"Stockholm"}',
            $this->hydrator->hydrate('{"city":"{{ address.city }}"}', ['address' => ['city' => 'Stockholm']])
        );
    }

    /** @testdox Resolves a deeply nested dot-notation path */
    public function testDeepDotNotationPath(): void
    {
        static::assertSame(
            '{"z":"deep"}',
            $this->hydrator->hydrate('{"z":"{{ a.b.c }}"}', ['a' => ['b' => ['c' => 'deep']]])
        );
    }

    /** @testdox Interpolates a placeholder inline within a string value */
    public function testInlinePlaceholderInterpolation(): void
    {
        static::assertSame(
            '{"msg":"Hello Bob!"}',
            $this->hydrator->hydrate('{"msg":"Hello {{ name }}!"}', ['name' => 'Bob'])
        );
    }

    /** @testdox Replaces multiple inline placeholders in a single string value */
    public function testMultipleInlinePlaceholders(): void
    {
        static::assertSame(
            '{"msg":"Jane Doe"}',
            $this->hydrator->hydrate('{"msg":"{{ first }} {{ last }}"}', ['first' => 'Jane', 'last' => 'Doe'])
        );
    }

    /** @testdox JSON-encodes a non-scalar value when used as an inline placeholder */
    public function testInlineNonScalarIsJsonEncoded(): void
    {
        static::assertSame(
            '{"msg":"tags: [\"php\",\"oop\"]"}',
            $this->hydrator->hydrate('{"msg":"tags: {{ tags }}"}', ['tags' => ['php', 'oop']])
        );
    }

    /** @testdox Returns empty string for a missing key */
    public function testMissingKeyReturnsEmptyString(): void
    {
        static::assertSame(
            '{"x":""}',
            $this->hydrator->hydrate('{"x":"{{ missing }}"}', [])
        );
    }

    /** @testdox Returns empty string for a null value */
    public function testNullValueReturnsEmptyString(): void
    {
        static::assertSame(
            '{"x":""}',
            $this->hydrator->hydrate('{"x":"{{ key }}"}', ['key' => null])
        );
    }

    /** @testdox Returns empty string inline for a missing key */
    public function testMissingKeyInlineReturnsEmptyString(): void
    {
        static::assertSame(
            '{"msg":"Hello !"}',
            $this->hydrator->hydrate('{"msg":"Hello {{ name }}!"}', [])
        );
    }

    /** @testdox Ignores whitespace around the key in a placeholder */
    public function testWhitespaceAroundKeyIsIgnored(): void
    {
        static::assertSame(
            '{"name":"Eve"}',
            $this->hydrator->hydrate('{"name":"{{  name  }}"}', ['name' => 'Eve'])
        );
    }

    /** @testdox Passes through a literal non-string scalar value in the template unchanged */
    public function testLiteralScalarValueIsPassedThrough(): void
    {
        static::assertSame(
            '{"score":9.5}',
            $this->hydrator->hydrate('{"score":9.5}', [])
        );
    }

    /** @testdox Replaces placeholders in nested template objects */
    public function testNestedTemplateObjectWithMultipleFields(): void
    {
        static::assertSame(
            '{"user":{"name":"Carl","age":30}}',
            $this->hydrator->hydrate(
                '{"user":{"name":"{{ name }}","age":"{{ age }}"}}',
                ['name' => 'Carl', 'age' => 30]
            )
        );
    }
}
