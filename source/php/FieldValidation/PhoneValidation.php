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
            'instructions' => $this->wpService->__('Use a country code starting with + or 00 for international numbers.', 'modularity-frontend-form'),
            'name' => 'phone_validation',
            'type' => 'select',
            'choices' => [
                'none' => $this->wpService->__('None', 'modularity-frontend-form'),
                'possible' => $this->wpService->__('Possible', 'modularity-frontend-form'),
            ],
            'default_value' => 'none',
        ]);
    }

    public function validate(mixed $valid, mixed $value, array $field): mixed
    {
        if ($valid !== true || ($field['phone_validation'] ?? 'none') !== 'possible' || $value === '' || $value === null) {
            return $valid;
        }

        if (is_string($value)) {
            try {
                $util = PhoneNumberUtil::getInstance();
                if ($util->isPossibleNumber($util->parse($value, 'SE'))) {
                    return true;
                }
            } catch (NumberParseException $exception) {
                // Report parsing failures in the same way as impossible numbers.
            }
        }

        return $this->wpService->__('Enter a possible phone number. For international numbers, include the country code starting with + or 00.', 'modularity-frontend-form');
    }
}
