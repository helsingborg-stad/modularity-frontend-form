<?php

namespace ModularityFrontendForm\FieldMapping\Mapper\Acf;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\FieldMapping\Mapper;
use PHPUnit\Framework\TestCase;
use WpService\WpService;

class PhoneNumberFieldMapperTest extends TestCase
{
    public function testPhoneFieldUsesTelephoneRenderingAndPreservesSettings(): void
    {
        $mapped = $this->mapper()->map($this->phoneField());

        self::assertSame('tel', $mapped['type']);
        self::assertSame('text', $mapped['view']);
        self::assertSame('field_phone', $mapped['name']);
        self::assertSame('Your phone number', $mapped['placeholder']);
        self::assertSame('+46701234567', $mapped['value']);
        self::assertTrue($mapped['required']);
        self::assertSame('tel', $mapped['attributeList']['data-js-field']);
        self::assertSame('field_phone', $mapped['attributeList']['data-js-field-name']);
        self::assertSame(
            [[['field' => 'field_enabled', 'operator' => '==', 'value' => '1']]],
            json_decode($mapped['attributeList']['data-js-conditional-logic'], true)
        );
    }

    public function testRepeaterPhonePreservesItsIdentityAndLocalCondition(): void
    {
        $phone = $this->phoneField();
        $phone['conditional_logic'] = [[['field' => 'field_phone', 'operator' => '!=', 'value' => '']]];
        $mapped = $this->mapper()->map([
            'type' => 'repeater', 'key' => 'field_contacts', 'label' => 'Contacts',
            'instructions' => '', 'required' => false, 'conditional_logic' => false,
            'wrapper' => ['id' => '', 'class' => ''],
            'min' => 0, 'max' => 5, 'button_label' => 'Add contact',
            'sub_fields' => [$phone],
        ]);
        $phone = $mapped['fields'][0];
        $identity = '[field_contacts][MOD_FRONTEND_FORM_REPEATER_ROW_INDEX_REPLACE][field_phone]';

        self::assertSame('tel', $phone['type']);
        self::assertSame($identity, $phone['name']);
        self::assertSame($identity, $phone['attributeList']['data-js-field-name']);
        self::assertSame(
            [[['field' => $identity, 'operator' => '!=', 'value' => '']]],
            json_decode($phone['attributeList']['data-js-conditional-logic'], true)
        );
        self::assertTrue($phone['required']);
        self::assertSame('Your phone number', $phone['placeholder']);
    }

    private function mapper(): Mapper
    {
        $wpService = $this->createMock(WpService::class);
        return new Mapper($wpService, (object) ['errorRequired' => 'Required'], new Config($wpService, 'test'));
    }

    private function phoneField(): array
    {
        return [
            'type' => 'phone_number', 'key' => 'field_phone', 'label' => 'Phone',
            'instructions' => '', 'required' => true,
            'wrapper' => ['id' => '', 'class' => ''],
            'placeholder' => 'Your phone number', 'default_value' => '+46701234567',
            'conditional_logic' => [[['field' => 'field_enabled', 'operator' => '==', 'value' => '1']]],
        ];
    }
}
