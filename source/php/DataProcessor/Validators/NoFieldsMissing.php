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

/**
 * Validator to ensure that no fields are missing from the submitted data
 * based on the selected groups in the module configuration.
 */
class NoFieldsMissing implements ValidatorInterface
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
    public function validate($data): ?ValidationResultInterface
    {
      $requiredFieldsInRequest = $this->moduleConfigInstance->getFieldKeysRegisteredAsFormFields('key');

      foreach($requiredFieldsInRequest as $fieldKey ) {
        if (!$this->array_key_exists_recursive($fieldKey, $data)) {
          $this->validationResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::ValidationError->value, 
              $this->wpService->__(
                'Form is missing required fields',
                'modularity-frontend-form'
              ), 
              [
                'fields' => [
                  'key' => $fieldKey
                ],
              ]
            )
          );
        }
      }
      return $this->validationResult;
    }

    /**
     * Recursively checks if a key exists in a multi-dimensional array
     *
     * @param string $key The key to search for
     * @param array $array The array to search in
     *
     * @return bool True if the key exists, false otherwise
     */
    private function array_key_exists_recursive($key, $array): bool
    {
      foreach ($array as $k => $value) {
        if ($k === $key) {
          return true;
        }
        if (is_array($value)) {
          if ($this->array_key_exists_recursive($key, $value)) {
            return true;
          }
        }
      }
      return false;
    }
}