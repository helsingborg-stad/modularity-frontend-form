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

class FieldsExists implements ValidatorInterface
{
    use GetModuleConfigInstanceTrait;

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
      $formData = is_array($data['mod-frontend-form'] ?? null) ? $data['mod-frontend-form'] : [];
      foreach ($formData as $key => $_value) {
          if (in_array($key, ['post_title', 'post_content'])) {
            continue;
          }

          if (\acf_get_field($key) === false) { //Todo: implement in acf service
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value, 
                $this->wpService->__(
                  'Form contains fields that do not exist',
                ), 
                [
                  'fields' => [
                    'key' => $key
                  ],
                ]
              )
            );
          }
        }
        
        return $this->validationResult;
    }

}