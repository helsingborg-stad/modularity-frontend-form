<?php

namespace ModularityFrontendForm\FieldFormatting;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\FieldValidation\PhoneValidation;

class PhoneNormalizer
{
    public function __construct(private PhoneValidation $validation, private ConfigInterface $config) {}

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
            if ($field['type'] === 'text') {
                $values[$key] = $this->validation->normalizeValue($value, null, $field);
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
