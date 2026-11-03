<?php

namespace ModularityFrontendForm\FieldValidation;

use PHPUnit\Framework\TestCase;
use WpService\WpService;

function acf_get_field($key) {
    return ['type' => $key === 'field_rows' ? 'repeater' : 'text', 'phone_validation' => $key === 'field_plain' ? 'none' : 'valid', 'maxlength' => 20];
}
function acf_add_validation_error($input, $message) {
    PhoneValidationTest::$nativeErrors[$input] = $message;
}

function acf_render_field_setting($field, $setting) {
    PhoneValidationTest::$renderedSetting = $setting;
}

class PhoneValidationTest extends TestCase
{
    private PhoneValidation $validation;
    public static array $nativeErrors = [];
    public static array $renderedSetting = [];

    public function testEditorChoices(): void
    {
        $this->validation->renderSetting([]);
        $this->assertSame(['none', 'valid'], array_keys(self::$renderedSetting['choices']));
        $this->assertSame('none', self::$renderedSetting['default_value']);
    }

    public function testNativePreflightRejectsMalformedValuesWithoutChangingValidStrings(): void
    {
        $originalPost = $_POST;
        try {
            self::$nativeErrors = ['other' => 'Earlier error'];
            $_POST['acf'] = ['field_phone' => ['123'], 'field_plain' => 'ordinary',
                'field_rows' => [0 => ['field_phone' => '0046 70 123 45 67'], 1 => ['field_phone' => ['123']]]];
            $this->validation->rejectMalformedNativeValues();
            $this->assertArrayNotHasKey('field_phone', $_POST['acf']);
            $this->assertSame('ordinary', $_POST['acf']['field_plain']);
            $this->assertSame('0046 70 123 45 67', $_POST['acf']['field_rows'][0]['field_phone']);
            $this->assertArrayNotHasKey('field_phone', $_POST['acf']['field_rows'][1]);
            $this->assertSame('Earlier error', self::$nativeErrors['other']);
            $this->assertArrayHasKey('acf[field_phone]', self::$nativeErrors);
            $this->assertArrayHasKey('acf[field_rows][1][field_phone]', self::$nativeErrors);
        } finally {
            $_POST = $originalPost;
        }
    }

    protected function setUp(): void
    {
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnCallback(fn($message) => $message);
        $this->validation = new PhoneValidation($wp);
    }

    public function testNormalizationUsesE164OnlyForAcceptedEnabledValues(): void
    {
        $field = ['phone_validation' => 'valid'];
        foreach (['070-123 45 67', '+46 70 123 45 67', '0046 70 123 45 67', '+46701234567'] as $value) {
            $this->assertSame('+46701234567', $this->validation->normalizeValue($value, 1, $field));
        }
        $this->assertSame('+4740612345', $this->validation->normalizeValue('0047 406 12 345', 1, $field));
        foreach (['', null, '0', '123', '070ABC1234', '++46701234567', [], '+46 10 000 00 00'] as $value) {
            $this->assertSame($value, $this->validation->normalizeValue($value, 1, $field));
        }
        foreach ([[], ['phone_validation' => 'none']] as $field) {
            $this->assertSame('070-123 45 67', $this->validation->normalizeValue('070-123 45 67', 1, $field));
        }
    }

    public function testOnlyEnabledPhoneFieldsGetAccessibilityMarker(): void
    {
        $plain = ['type' => 'text', 'wrapper' => ['class' => 'existing']];
        $this->assertSame($plain, $this->validation->markPhoneField($plain));
        $marked = $this->validation->markPhoneField($plain + ['phone_validation' => 'valid']);
        $this->assertSame('true', $marked['wrapper']['data-phone-validation']);
        $this->assertSame('existing', $marked['wrapper']['class']);
    }

    /** @dataProvider validNumbers */
    public function testValidNumbers(string $number): void
    {
        $this->assertTrue($this->validation->validate(true, $number, ['phone_validation' => 'valid']));
    }

    public function testStrictModeUsesLibraryValidityWithoutChangingOtherModes(): void
    {
        foreach (['070-123 45 67', '+46 70 123 45 67', '0046 70 123 45 67', '+47 406 12 345', '0047 406 12 345'] as $value) {
            $this->assertTrue($this->validation->validate(true, $value, ['phone_validation' => 'valid']));
        }
        foreach (['+46 10 000 00 00', '73209899', '073209899', '+12223333333'] as $value) {
            $this->assertIsString($this->validation->validate(true, $value, ['phone_validation' => 'valid']));
        }
        $this->assertTrue($this->validation->validate(true, '', ['phone_validation' => 'valid']));
        $this->assertSame('Required', $this->validation->validate('Required', '', ['phone_validation' => 'valid']));
        $this->assertIsString($this->validation->validate(true, '0', ['phone_validation' => 'valid']));
        $this->assertIsString($this->validation->validate(true, [], ['phone_validation' => 'valid']));
    }

    /** @dataProvider formatInputs */
    public function testFormatRule(string $value, bool $accepted): void
    {
        $result = $this->validation->validate(true, $value, ['phone_validation' => 'valid']);
        if ($accepted) {
            $this->assertTrue($result);
        } else {
            $this->assertSame('Use digits, spaces, hyphens, dots or parentheses, with an optional + at the start.', $result);
        }
        $this->assertTrue($this->validation->validate(true, $value, ['phone_validation' => 'none']));
        $this->assertSame('Earlier error', $this->validation->validate('Earlier error', $value, ['phone_validation' => 'valid']));
    }

    public static function formatInputs(): array
    {
        return [
            ['070-123 45 67', true], ['070.123.45.67', true], ['070 (123) 45 67', true],
            ['+46 70 123 45 67', true], ['0047 406 12 345', true],
            ['070ABC4567', false], ['070/1234567', false], ['++46701234567', false],
            ['070+1234567', false], ["0701234567\n", false], ["070\t1234567", false],
            ['070ABC1234', false],
        ];
    }

    public static function validNumbers(): array
    {
        return array_map(fn($number) => [$number], [
            '070-123 45 67', '08-123 456 78', '+46 70 123 45 67',
            '0046 70 123 45 67', '+47 406 12 345', '0047 406 12 345',
            '070 (123) 45 67', '40612345',
        ]);
    }

    public function testSkipsAndPreservesExistingErrors(): void
    {
        $this->assertTrue($this->validation->validate(true, 'bad', []));
        $this->assertTrue($this->validation->validate(true, 'bad', ['phone_validation' => 'none']));
        $this->assertTrue($this->validation->validate(true, '', ['phone_validation' => 'valid']));
        $this->assertFalse($this->validation->validate(false, '', ['phone_validation' => 'valid']));
        $this->assertSame('Earlier error', $this->validation->validate('Earlier error', 'bad', ['phone_validation' => 'valid']));
    }

    /** @dataProvider badNumbers */
    public function testRejectsBadNumbers(mixed $number): void
    {
        $this->assertIsString($this->validation->validate(true, $number, ['phone_validation' => 'valid']));
    }

    public static function badNumbers(): array
    {
        return [['123'], ['abcdefgh'], ['0'], [0], [[]], [new \stdClass()]];
    }
}
