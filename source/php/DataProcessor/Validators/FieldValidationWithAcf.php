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
    ) {
    }

    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
        $namespace = $this->config->getFieldNamespace();
        $fields = $data[$namespace] ?? [];
        $fallback = $this->wpService->__('Field validation failed', 'modularity-frontend-form');
        $this->validationResult = new ValidationResult();

        acf_reset_validation_errors();
        foreach ($fields as $key => $value) {
            if (in_array($key, $this->config->getKeysToBypass(), true)) {
                continue;
            }

            if ($field = acf_get_field($key)) {
                $input = $namespace . '[' . $key . ']';
                $before = acf_get_validation_errors() ?: [];
                // ACF validates repeater children with their row-specific input paths.
                $valid = acf_validate_value($value, $field, $input);
                if (!$valid && count(acf_get_validation_errors() ?: []) === count($before)) {
                    acf_add_validation_error($input, $fallback);
                }
            }
        }

        $errors = acf_get_validation_errors() ?: [];
        acf_reset_validation_errors();
        if ($errors) {
            $fieldErrors = [];
            foreach ($errors as $error) {
                $fieldErrors[] = [
                    'input' => $error['input'] ?? '',
                    'message' => is_string($error['message'] ?? null) && trim($error['message']) !== ''
                        ? $error['message'] : $fallback,
                ];
            }
            $this->validationResult->setError(new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value,
                $fieldErrors[0]['message'],
                ['status' => 400, 'field_errors' => $fieldErrors]
            ));
        }

        return $this->validationResult;
    }
}
