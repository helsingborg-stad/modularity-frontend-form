<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use PHPUnit\Framework\TestCase;

final class MultipartDiagnosticsTest extends TestCase
{
    public function testDiagnosticsDistinguishNestedFieldsFromJsonTextWithoutValues(): void
    {
        $encoder = new MultipartFormDataEncoder();
        $nested = $encoder->encode(['location' => ['lat' => '59.325', 'address' => 'PRIVATE_ADDRESS']]);
        self::assertSame(['location[lat]', 'location[address]'], $nested['fieldNames']);
        self::assertSame([], $nested['fileFields']);
        self::assertStringContainsString('name="location[lat]"', $nested['body']);

        $text = $encoder->encode(['location' => '{"address":"PRIVATE_ADDRESS"}']);
        self::assertSame(['location'], $text['fieldNames']);
        self::assertStringContainsString('name="location"', $text['body']);
        unset($nested['body'], $text['body']);
        self::assertStringNotContainsString('PRIVATE_ADDRESS', json_encode([$nested, $text]));
    }
}
