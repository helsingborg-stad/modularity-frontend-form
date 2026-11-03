<?php

namespace ModularityFrontendForm\FieldValidation;

use PHPUnit\Framework\TestCase;
use WpService\WpService;

function acf_get_field($key) {
    return ['type' => $key === 'field_rows' ? 'repeater' : 'text', 'phone_validation' => $key === 'field_plain' ? 'none' : 'possible', 'maxlength' => 20];
}
function acf_add_validation_error($input, $message) {
    PhoneValidationTest::$nativeErrors[$input] = $message;
}

class PhoneValidationTest extends TestCase
{
    private PhoneValidation $validation;
    public static array $nativeErrors = [];

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

    /** @dataProvider possibleNumbers */
    public function testPossibleNumbers(string $number): void
    {
        $this->assertTrue($this->validation->validate(true, $number, ['phone_validation' => 'possible']));
    }

    public function testStrictModeUsesLibraryValidityWithoutChangingOtherModes(): void
    {
        foreach (['070-123 45 67', '+46 70 123 45 67', '0046 70 123 45 67', '+47 406 12 345', '0047 406 12 345'] as $value) {
            $this->assertTrue($this->validation->validate(true, $value, ['phone_validation' => 'valid']));
        }
        $value = '+46 10 000 00 00';
        $this->assertTrue($this->validation->validate(true, $value, ['phone_validation' => 'possible']));
        $this->assertIsString($this->validation->validate(true, $value, ['phone_validation' => 'valid']));
        $this->assertTrue($this->validation->validate(true, '', ['phone_validation' => 'valid']));
        $this->assertSame('Required', $this->validation->validate('Required', '', ['phone_validation' => 'valid']));
        $this->assertIsString($this->validation->validate(true, '0', ['phone_validation' => 'valid']));
        $this->assertIsString($this->validation->validate(true, [], ['phone_validation' => 'valid']));
    }

    public static function possibleNumbers(): array
    {
        return array_map(fn($number) => [$number], [
            '070-123 45 67', '08-123 456 78', '+46 70 123 45 67',
            '0046 70 123 45 67', '+47 406 12 345', '0047 406 12 345',
            '070 (123) 45 67', '+46 10 000 00 00', '40612345',
        ]);
    }

    public function testSkipsAndPreservesExistingErrors(): void
    {
        $this->assertTrue($this->validation->validate(true, 'bad', []));
        $this->assertTrue($this->validation->validate(true, 'bad', ['phone_validation' => 'none']));
        $this->assertTrue($this->validation->validate(true, '', ['phone_validation' => 'possible']));
        $this->assertFalse($this->validation->validate(false, '', ['phone_validation' => 'possible']));
        $this->assertSame('Earlier error', $this->validation->validate('Earlier error', 'bad', ['phone_validation' => 'possible']));
    }

    /** @dataProvider badNumbers */
    public function testRejectsBadNumbers(mixed $number): void
    {
        $this->assertIsString($this->validation->validate(true, $number, ['phone_validation' => 'possible']));
    }

    public static function badNumbers(): array
    {
        return [['123'], ['abcdefgh'], ['0'], [0], [[]], [new \stdClass()]];
    }
}
