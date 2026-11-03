<?php

namespace ModularityFrontendForm\FieldValidation;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use WpService\WpService;

class PhoneValidation
{
    public function __construct(private WpService $wpService) {}

    public function addHooks(): void
    {
        $this->wpService->addAction('acf/render_field_settings/type=text', [$this, 'renderSetting']);
        $this->wpService->addFilter('acf/validate_value/type=text', [$this, 'validate'], 10, 3);
    }

    public function renderSetting(array $field): void
    {
        acf_render_field_setting($field, [
            'label' => $this->wpService->__('Phone validation', 'modularity-frontend-form'),
            'instructions' => $this->wpService->__('None disables phone validation. Possible is lenient and recommended when validation is wanted. Valid is strict. Neither proves that a number exists or is reachable. Use a country code starting with + or 00 for international numbers.', 'modularity-frontend-form'),
            'name' => 'phone_validation',
            'type' => 'select',
            'choices' => [
                'none' => $this->wpService->__('None', 'modularity-frontend-form'),
                'possible' => $this->wpService->__('Possible', 'modularity-frontend-form'),
                'valid' => $this->wpService->__('Valid', 'modularity-frontend-form'),
            ],
            'default_value' => 'none',
        ]);
    }

    public function validate(mixed $valid, mixed $value, array $field): mixed
    {
        $mode = $field['phone_validation'] ?? 'none';
        if ($valid !== true || !in_array($mode, ['possible', 'valid'], true) || $value === '' || $value === null) {
            return $valid;
        }

        if (is_string($value)) {
            try {
                $util = PhoneNumberUtil::getInstance();
                $number = $util->parse($value, 'SE');
                if ($mode === 'valid' ? $util->isValidNumber($number) : $util->isPossibleNumber($number)) {
                    return true;
                }
            } catch (NumberParseException $exception) {
                // Report parsing failures in the same way as impossible numbers.
            }
        }

        return $this->wpService->__('Check the phone number. For international numbers, include the country code starting with + or 00.', 'modularity-frontend-form');
    }
}
