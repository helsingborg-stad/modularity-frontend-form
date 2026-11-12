<?php

namespace ModularityFrontendForm\FieldFormatting;

use AcfPhoneNumberField\PhoneFormatter;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Hookable\Hookable;
use WpService\WpService;

class PhoneIntegration implements Hookable
{
    public function __construct(private WpService $wpService, private ConfigInterface $config) {}

    public function addHooks(): void
    {
        $this->wpService->addFilter('ModularityFrontendForm/Submission/Data', [$this, 'normalize']);
        $this->wpService->addFilter('ModularityFrontendForm/Field/DisplayValue', [$this, 'display'], 10, 2);
        $this->wpService->addFilter('acf/validate_value/type=phone_number', [$this, 'validateRegistration']);
    }

    public function validateRegistration(mixed $valid): mixed
    {
        if ($valid !== true || acf_get_field_type('phone_number')) {
            return $valid;
        }
        return $this->wpService->__('Field validation failed', 'modularity-frontend-form');
    }

    public function display(mixed $value, array $field): mixed
    {
        if ($field['type'] !== 'phone_number') {
            return $value;
        }
        if (!acf_get_field_type('phone_number')) {
            throw new \RuntimeException('ACF Phone Number Field must be installed and registered.');
        }
        return (new PhoneFormatter())->display(
            $value,
            $field['display_format'] ?? 'auto',
            $field['default_region'] ?? 'SE'
        );
    }

    public function normalize(array $data): array
    {
        $namespace = $this->config->getFieldNamespace();
        if (isset($data[$namespace]) && is_array($data[$namespace])) {
            $data[$namespace] = $this->normalizeFields($data[$namespace]);
        }
        return $data;
    }

    private function normalizeFields(array $values): array
    {
        foreach ($values as $key => $value) {
            $field = is_string($key) && str_starts_with($key, 'field_') ? acf_get_field($key) : false;
            if (!$field) {
                continue;
            }
            if ($field['type'] === 'phone_number') {
                $phone = acf_get_field_type('phone_number');
                if (!$phone) {
                    throw new \RuntimeException('ACF Phone Number Field must be installed and registered.');
                }
                $values[$key] = $phone->update_value($value, null, $field);
            } elseif ($field['type'] === 'repeater' && is_array($value)) {
                foreach ($value as $row => $children) {
                    if (is_array($children)) {
                        $values[$key][$row] = $this->normalizeFields($children);
                    }
                }
            }
        }
        return $values;
    }
}
