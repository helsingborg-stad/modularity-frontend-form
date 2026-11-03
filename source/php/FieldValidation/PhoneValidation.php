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
        $this->wpService->addAction('acf/validate_save_post', [$this, 'rejectMalformedNativeValues'], 4);
        $this->wpService->addFilter('acf/validate_value/type=text', [$this, 'validate'], 10, 3);
    }

    public function rejectMalformedNativeValues(): void
    {
        if (isset($_POST['acf']) && is_array($_POST['acf'])) {
            $this->checkNativeValues($_POST['acf'], 'acf');
        }
    }

    private function checkNativeValues(array &$values, string $prefix): void
    {
        foreach ($values as $key => &$value) {
            $input = "{$prefix}[{$key}]";
            $field = is_string($key) && str_starts_with($key, 'field_') ? acf_get_field($key) : false;
            if ($field && ($field['type'] ?? '') === 'text'
                && in_array($field['phone_validation'] ?? 'none', ['possible', 'valid'], true)
                && !is_string($value) && $value !== null
            ) {
                acf_add_validation_error($input, $this->validate(true, $value, $field));
                // ACF's native maxlength filter cannot handle arrays, even after validation fails.
                unset($values[$key]);
            } elseif (is_array($value)) {
                $this->checkNativeValues($value, $input);
            }
        }
        unset($value);
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
