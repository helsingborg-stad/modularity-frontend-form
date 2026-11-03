<?php

namespace ModularityFrontendForm\FieldValidation;

use PHPUnit\Framework\TestCase;
use WpService\WpService;

class PhoneValidationTest extends TestCase
{
    private PhoneValidation $validation;

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
