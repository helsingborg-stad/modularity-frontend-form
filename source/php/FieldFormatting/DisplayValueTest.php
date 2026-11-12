<?php

namespace ModularityFrontendForm\FieldFormatting;

use PHPUnit\Framework\TestCase;
use WpService\WpService;

class DisplayValueTest extends TestCase
{
    public function testFormatsKeyAndNameIndexedRepeaterChildrenWithoutChangingOtherValues(): void
    {
        $wp = $this->createMock(WpService::class);
        $wp->method('applyFilters')->willReturnCallback(function ($hook, $value, $field) {
            self::assertSame('ModularityFrontendForm/Field/DisplayValue', $hook);
            return $field['type'] === 'custom' ? "display:$value" : $value;
        });
        $field = ['type' => 'repeater', 'sub_fields' => [
            ['key' => 'field_custom', 'name' => 'custom', 'type' => 'custom'],
            ['key' => 'field_text', 'name' => 'text', 'type' => 'text'],
        ]];
        $value = [
            0 => ['field_custom' => 'one', 'field_text' => 'unchanged'],
            2 => ['custom' => 'two', 'text' => 'also unchanged'],
        ];
        $expected = $value;
        $expected[0]['field_custom'] = 'display:one';
        $expected[2]['custom'] = 'display:two';
        self::assertSame($expected, (new DisplayValue($wp))->format($value, $field));
        self::assertSame('one', $value[0]['field_custom']);
    }
}
