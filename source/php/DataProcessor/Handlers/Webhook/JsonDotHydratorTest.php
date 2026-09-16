<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use PHPUnit\Framework\TestCase;

class JsonDotHydratorTest extends TestCase
{
    /** @dataProvider hydrationExamples */
    public function testHydrationExamples(string $template, array $data, string $expected): void
    {
        self::assertSame($expected, (new JsonDotHydrator())->hydrate($template, $data));
    }

    public static function hydrationExamples(): array
    {
        return [
            'invalid JSON' => ['not json', [], 'not json'],
            'empty object' => ['{}', ['x' => 1], '[]'],
            'no placeholders' => ['{"name":"Alice"}', [], '{"name":"Alice"}'],
            'string' => ['{"name":"{{ name }}"}', ['name' => 'Alice'], '{"name":"Alice"}'],
            'integer' => ['{"count":"{{ count }}"}', ['count' => 42], '{"count":42}'],
            'boolean' => ['{"active":"{{ active }}"}', ['active' => true], '{"active":true}'],
            'array' => ['{"items":"{{ items }}"}', ['items' => ['a', 'b']], '{"items":["a","b"]}'],
            'dot notation' => ['{"city":"{{ address.city }}"}', ['address' => ['city' => 'Stockholm']], '{"city":"Stockholm"}'],
            'deep path' => ['{"z":"{{ a.b.c }}"}', ['a' => ['b' => ['c' => 'deep']]], '{"z":"deep"}'],
            'interpolation' => ['{"msg":"Hello {{ name }}!"}', ['name' => 'Bob'], '{"msg":"Hello Bob!"}'],
            'multiple placeholders' => ['{"msg":"{{ first }} {{ last }}"}', ['first' => 'Jane', 'last' => 'Doe'], '{"msg":"Jane Doe"}'],
            'inline array' => ['{"msg":"tags: {{ tags }}"}', ['tags' => ['php', 'oop']], '{"msg":"tags: [\"php\",\"oop\"]"}'],
            'missing' => ['{"x":"{{ missing }}"}', [], '{"x":""}'],
            'null' => ['{"x":"{{ key }}"}', ['key' => null], '{"x":""}'],
            'missing inline' => ['{"msg":"Hello {{ name }}!"}', [], '{"msg":"Hello !"}'],
            'whitespace' => ['{"name":"{{  name  }}"}', ['name' => 'Eve'], '{"name":"Eve"}'],
            'literal' => ['{"score":9.5}', [], '{"score":9.5}'],
            'nested template' => [
                '{"user":{"name":"{{ name }}","age":"{{ age }}"}}',
                ['name' => 'Carl', 'age' => 30], '{"user":{"name":"Carl","age":30}}',
            ],
        ];
    }
}
