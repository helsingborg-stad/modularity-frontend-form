<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use WpService\WpService;

class AcfValidateJsonField implements \Municipio\HooksRegistrar\Hookable
{
    static $fieldId = 'field_6a0abc4eef652';

    public function __construct(private WpService $wpService) {}

    public function addHooks(): void
    {
        $this->wpService->addFilter(
            'acf/validate_value/key=' . self::$fieldId,
            [$this, 'validateJsonValue'],
            10,
            3
        );
    }

    public function validateJsonValue(bool|string $valid, mixed $value, array $_field): bool|string
    {
        if (!$valid || empty($value)) {
            return $valid;
        }

        json_decode(stripslashes($value), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return sprintf(
                /* translators: %s: JSON parse error message */
                $this->wpService->__('Invalid JSON: %s', 'modularity-frontend-form'),
                json_last_error_msg()
            );
        }

        return $valid;
    }
}
