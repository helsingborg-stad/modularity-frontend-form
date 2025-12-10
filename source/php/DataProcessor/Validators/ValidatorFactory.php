<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\DataProcessor\Validators\FieldsExistsOnPostType;
use ModularityFrontendForm\DataProcessor\Validators\FieldValidationWithAcf;
use ModularityFrontendForm\DataProcessor\Validators\NonceValidator;
use ModularityFrontendForm\Config\ModuleConfigFactoryInterface;
use ModularityFrontendForm\DataProcessor\Validators\NoFieldsMissing;
class ValidatorFactory {

  use GetModuleConfigInstanceTrait;

  public function __construct(
    private WpService $wpService,
    private AcfService $acfService,
    private ConfigInterface $config,
    private ModuleConfigFactoryInterface $moduleConfigFactory
  ) {}

  /**
   * Creates an array of validators for the read/get process
   *
   * @param int $moduleId The module ID
   *
   * @return ValidatorInterface[] An array of validators
   */
  public function createGetValidators(int $moduleId): array {
    $args = $this->createValidatorInterfaceRequiredArguments($moduleId);
    
    return array_unique(
      array_merge(
        $this->createInsertValidators($moduleId), 
        [
          new IsEditableValidator(...$args)
        ]
      )
    );
  }

  /**
   * Creates an array of validators for the update process
   *
   * @param int $moduleId The module ID
   *
   * @return ValidatorInterface[] An array of validators
   */
  public function createUpdateValidators(int $moduleId): array {
      $args = $this->createValidatorInterfaceRequiredArguments($moduleId);
      
      //Feature toggles
      $useIsEditable = true;

      $insertValidators =  $this->createInsertValidators($moduleId); 
      return array_filter($insertValidators + [
        $useIsEditable ? new IsEditableValidator(...$args) : null,
      ]);
  }

  /**
   * Creates an array of validators for the insert process
   *
   * @param int $moduleId The module ID
   *
   * @return ValidatorInterface[] An array of validators
   */
  public function createInsertValidators(int $moduleId): array {

      $config = $this->getModuleConfigInstance($moduleId);

      //Feature toggles
      $useNoFieldMissing          = true;
      $useFieldValidationWithAcf  = true;

      //Check if the module is configured to use the WPDB handler
      //This configuration allows us to validate that the fields
      //exist on the post type.
      //This will gracefully degrade to a simple field existence check
      //if the WPDB handler is not configured.
      $useFieldsExistsOnPostType = (
        $config->getWpDbHandlerConfig() !== null
      ) ? true : false;
      $useFieldsExists = !$useFieldsExistsOnPostType;

      $args = $this->createValidatorInterfaceRequiredArguments($moduleId);

      return array_filter([
          $useNoFieldMissing          ? new NoFieldsMissing(...$args) : null,
          $useFieldsExistsOnPostType  ? new FieldsExistsOnPostType(...$args) : null,
          $useFieldsExists            ? new FieldsExists(...$args) : null,
          $useFieldValidationWithAcf  ? new FieldValidationWithAcf(...$args) : null,
      ]);
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