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

class FieldsExistsOnPostType implements ValidatorInterface
{
    use GetModuleConfigInstanceTrait;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($data): ?ValidationResultInterface
    {
      $validationResult = new ValidationResult();
      foreach ($data as $key => $value) {
          if($field = acf_get_field($key)) { //TODO: Add to wp service
              $isValid = acf_validate_value($value, $field, ""); //TODO: Add to wp service
              if(!$isValid) {
                $validationResult->setError(
                  new WP_Error(
                    "validation_error", 
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
        
        return $validationResult;
    }

}