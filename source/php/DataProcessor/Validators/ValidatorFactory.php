<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\Validators\FieldsExistsOnPostType;
use ModularityFrontendForm\DataProcessor\Validators\FieldValidationWithAcf;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
class ValidatorFactory {

  use GetModuleConfigInstanceTrait;

  public function __construct(
    private WpService $wpService,
    private AcfService $acfService,
    private ConfigInterface $config,
    private ModuleConfigFactoryInterface $moduleConfigFactory
  ) {}

  /**
   * Creates an array of validators for the update process
   *
   * @param int $moduleId The module ID
   *
   * @return ValidatorInterface[] An array of validators
   */
  public function createUpdateValidators(int $moduleId): array {
      $args = $this->createValidatorInterfaceRequiredArguments($moduleId);
      
      return array_unique(
        array_merge(
          $this->createInsertValidators($moduleId), 
          []
        )
      );
  }

  /**
   * Creates an array of validators for the insert process
   *
   * @param int $moduleId The module ID
   *
   * @return ValidatorInterface[] An array of validators
   */
  public function createInsertValidators(int $moduleId): array {
      $args = $this->createValidatorInterfaceRequiredArguments($moduleId);
      return  [
          //new NonceValidator(...$args),
          new FieldsExistsOnPostType(...$args),
          new FieldValidationWithAcf(...$args),
      ];
  }

  /**
   * Creates a array representing the arguments for the validator
   */
  private function createValidatorInterfaceRequiredArguments(int $moduleId): array {
    return [
      $this->wpService,
      $this->acfService,
      $this->config,
      $this->getModuleConfigInstance($moduleId),
    ];
  }
}