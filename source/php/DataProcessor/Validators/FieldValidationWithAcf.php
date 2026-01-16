<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use WP_Error;
use WpService\WpService;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_REST_Request;

class FieldValidationWithAcf implements ValidatorInterface
{
    use GetModuleConfigInstanceTrait;

    //Data keys that should be ignored during validation of this validator
    private array $bypassValidationForKeys = [
      'nonce'
    ];

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private ValidationResultInterface $validationResult = new ValidationResult()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
      $this->recursiveValidate($data);
      return $this->validationResult;
    }

    /**
     * Recursively validate fields and subfields.
     *
     * @param array $data
     * @return void
     */
    private function recursiveValidate($data)
    {
      foreach ($data as $key => $value) {
        if (in_array($key, $this->config->getKeysToBypass())) {
          continue;
        }

        if ($this->isSubFieldCollection($value)) {
          $this->recursiveValidate($value);
          continue;
        }

        if ($field = acf_get_field($key)) { //TODO: Add to acf service
          $isValid = acf_validate_value($value, $field, ""); //TODO: Add to acf service

          if (!$isValid) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value,
                $this->wpService->__(
                  'Field validation failed',
                ),
                [
                  'fields' => [
                    'key' => $key,
                    'label' => $field['label'] ?? $key
                  ],
                ]
              )
            );
          }
        }
      }
    }

    /**
     * Check if the given value is a collection of sub fields.
     *
     * @param mixed $value
     * @return bool
     */
    private function isSubFieldCollection(mixed $value): bool
    {
      if (!is_array($value)) {
        return false;
      }

      foreach($value as $subValue) {
        if (!is_array($subValue)) {
          return false;
        }

        foreach($subValue as $subKey => $subSubValue) {
          if (str_starts_with($subKey, 'field_')) {
            return true;
          }
        }
      } 
      return false;
    }

}