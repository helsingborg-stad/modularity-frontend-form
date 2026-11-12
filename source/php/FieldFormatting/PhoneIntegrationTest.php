<?php

namespace ModularityFrontendForm\FieldFormatting;

use ModularityFrontendForm\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;
use WpService\WpService;

class PhoneIntegrationTest extends TestCase
{
    private PhoneIntegration $integration;
    private array $filters = [];

    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
        $wp = $this->createMock(WpService::class);
        $wp->method('__')->willReturnArgument(0);
        $wp->method('addFilter')->willReturnCallback(function ($hook, $callback) {
            $this->filters[$hook] = $callback;
            return true;
        });
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getFieldNamespace')->willReturn('form');
        $this->integration = new PhoneIntegration($wp, $config);
        $this->integration->addHooks();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    public function testMissingRegistrationRejectsWithoutReplacingEarlierAcfErrors(): void
    {
        \Brain\Monkey\Functions\when('acf_get_field_type')->justReturn(false);
        $validate = $this->filters['acf/validate_value/type=phone_number'];
        self::assertSame('Field validation failed', $validate(true));
        self::assertSame('Earlier error', $validate('Earlier error'));
        self::assertFalse($validate(false));
    }

    public function testRegisteredFieldKeepsAcfValidationResult(): void
    {
        \Brain\Monkey\Functions\when('acf_get_field_type')->justReturn(new \stdClass());
        self::assertTrue(($this->filters['acf/validate_value/type=phone_number'])(true));
    }

    public function testSubmissionFilterDelegatesNationalAndRepeaterValuesToRegisteredField(): void
    {
        $field = ['type' => 'phone_number', 'default_region' => 'US', 'return_format' => 'array'];
        $phone = $this->getMockBuilder(\stdClass::class)->addMethods(['update_value'])->getMock();
        $phone->expects($this->exactly(3))->method('update_value')->willReturnCallback(
            function ($value, $postId, $definition) use ($field) {
                self::assertNull($postId);
                self::assertSame($field, $definition);
                return $value === '' ? '' : '+12025550123';
            }
        );
        \Brain\Monkey\Functions\expect('acf_get_field_type')->with('phone_number')->times(3)->andReturn($phone);
        \Brain\Monkey\Functions\when('acf_get_field')->alias(fn($key) => match ($key) {
            'field_phone', 'field_empty' => $field,
            'field_rows' => ['type' => 'repeater'],
            default => ['type' => 'text'],
        });
        $data = ['nonce' => 'unchanged', 'form' => [
            'field_phone' => '202-555-0123', 'field_text' => 'unchanged', 'field_empty' => '',
            'field_rows' => [2 => ['field_phone' => '202-555-0123']],
        ]];
        $normalized = ($this->filters['ModularityFrontendForm/Submission/Data'])($data);
        self::assertSame('+12025550123', $normalized['form']['field_phone']);
        self::assertSame('+12025550123', $normalized['form']['field_rows'][2]['field_phone']);
        self::assertSame('', $normalized['form']['field_empty']);
        self::assertSame('unchanged', $normalized['form']['field_text']);
        self::assertSame('unchanged', $normalized['nonce']);
        self::assertSame('202-555-0123', $data['form']['field_phone']);
    }

    public function testDisplayFilterUsesDisplaySettingsInsteadOfReturnFormat(): void
    {
        \Brain\Monkey\Functions\when('acf_get_field_type')->justReturn(new \stdClass());
        $formatter = \Mockery::mock('overload:AcfPhoneNumberField\\PhoneFormatter');
        $formatter->shouldReceive('display')->once()->with('+12025550123', 'national', 'US')->andReturn('202-555-0123');
        $display = $this->filters['ModularityFrontendForm/Field/DisplayValue'];
        self::assertSame('202-555-0123', $display('+12025550123', [
            'type' => 'phone_number', 'display_format' => 'national',
            'default_region' => 'US', 'return_format' => 'array',
        ]));
        self::assertSame('ordinary', $display('ordinary', ['type' => 'text']));
    }

    public function testMissingRegistrationCannotNormalizePhoneValues(): void
    {
        \Brain\Monkey\Functions\when('acf_get_field_type')->justReturn(false);
        \Brain\Monkey\Functions\when('acf_get_field')->justReturn(['type' => 'phone_number']);
        $this->expectException(\RuntimeException::class);
        $this->integration->normalize(['form' => ['field_phone' => '202-555-0123']]);
    }
}
