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
    public function validate($data): ?ValidationResultInterface
    {
      foreach ($data as $key => $value) {

          // Check if the field key is in the bypass list
          if(in_array($key, $this->bypassValidationForKeys)) {
            continue;
          }

          if($field = acf_get_field($key)) { //TODO: Add to acf service
              $isValid = acf_validate_value($value, $field, ""); //TODO: Add to acf service
              if(!$isValid) {
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
        
        return $this->validationResult;
    }

}