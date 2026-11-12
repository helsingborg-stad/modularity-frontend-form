<?php

namespace ModularityFrontendForm\FieldFormatting;

use WpService\WpService;

class DisplayValue
{
    public function __construct(private WpService $wpService) {}

    public function format(mixed $value, array $field): mixed
    {
        if ($field['type'] === 'repeater' && is_array($value)) {
            foreach ($value as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach ($field['sub_fields'] ?? [] as $child) {
                    // ACF raw reads use keys; initial/formatted rows can use names.
                    $key = array_key_exists($child['key'], $row) ? $child['key'] : $child['name'];
                    if (array_key_exists($key, $row)) {
                        $value[$index][$key] = $this->format($row[$key], $child);
                    }
                }
            }
        }
        return $this->wpService->applyFilters('ModularityFrontendForm/Field/DisplayValue', $value, $field);
    }
}
