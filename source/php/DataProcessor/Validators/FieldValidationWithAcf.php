<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_Error;
use WP_REST_Request;
use WpService\WpService;

class FieldValidationWithAcf implements ValidatorInterface
{
    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private ValidationResultInterface $validationResult = new ValidationResult()
    ) {}

    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
        $namespace = $this->config->getFieldNamespace();
        $fields = $data[$namespace] ?? [];
        if (!is_array($fields)) {
            $this->addError('', '', $this->fallbackMessage());
            return $this->validationResult;
        }

        // ACF validates repeater children itself and preserves their full input paths.
        foreach ($fields as $key => $value) {
            if (!is_string($key) || in_array($key, $this->config->getKeysToBypass(), true)) {
                continue;
            }
            $field = acf_get_field($key);
            if (!$field) {
                continue;
            }
            $input = $this->config->getFieldNamespace($key);
            if (!$this->hasValidShape($value, $field, $input)) {
                continue;
            }
            $before = count(acf_get_validation_errors() ?: []);
            $valid = acf_validate_value($value, $field, $input);
            $errors = array_slice(acf_get_validation_errors() ?: [], $before);
            foreach ($errors as $error) {
                $this->addError($key, $error['input'] ?: $input, $error['message'], $field['label'] ?? $key);
            }
            if (!$valid && !$errors) {
                $this->addError($key, $input, $this->fallbackMessage(), $field['label'] ?? $key);
            }
        }
        return $this->validationResult;
    }

    // Reject malformed text before ACF's maxlength check can call string functions on arrays.
    private function hasValidShape(mixed $value, array $field, string $input): bool
    {
        if (($field['type'] ?? '') === 'text' && !is_string($value) && $value !== null) {
            $this->addError($field['key'], $input, $this->fallbackMessage(), $field['label'] ?? '');
            return false;
        }
        if (($field['type'] ?? '') === 'repeater' && is_array($value)) {
            foreach ($value as $index => $row) {
                if ($index === 'acfcloneindex' || str_contains((string) $index, '_deleted')) {
                    continue;
                }
                if (!is_array($row)) {
                    $this->addError($field['key'], $input, $this->fallbackMessage(), $field['label'] ?? '');
                    return false;
                }
                foreach ($field['sub_fields'] ?? [] as $child) {
                    if (array_key_exists($child['key'], $row)
                        && !$this->hasValidShape($row[$child['key']], $child, "{$input}[{$index}][{$child['key']}]")
                    ) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function fallbackMessage(): string
    {
        return $this->wpService->__('Field validation failed', 'modularity-frontend-form');
    }

    private function addError(string $key, string $input, string $message, string $label = ''): void
    {
        $this->validationResult->setError(new WP_Error(
            RestApiResponseStatusEnums::ValidationError->value,
            $message,
            ['status' => 400, 'fields' => ['key' => $key, 'label' => $label, 'input' => $input]]
        ));
    }
}
