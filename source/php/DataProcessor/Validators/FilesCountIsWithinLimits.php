<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use WP_Error;
use WpService\WpService;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_REST_Request;
use ModularityFrontendForm\Helper\FilesArrayFormatterInterface;
use ModularityFrontendForm\Helper\FilesArrayFormatter;

class FilesCountIsWithinLimits implements ValidatorInterface
{
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
    public function validate(array $data, WP_REST_Request $request, ?FilesArrayFormatterInterface $filesArrayFormatter = null): ?ValidationResultInterface
    {
      if($filesArrayFormatter === null) {
        $filesArrayFormatter = new FilesArrayFormatter($request, $this->config);
      }

      if(!$formattedFilesArray = $filesArrayFormatter->getFormatted()) {
        return $this->validationResult;
      }

      $checkedFieldKeys = [];

      foreach($formattedFilesArray as $fieldKey => $filesArray) {

        if(in_array($fieldKey, $checkedFieldKeys)) {
          continue;
        }

        $fieldMaxItems = $this->getFieldConstraints($fieldKey, 'max');
        $fieldMinItems = $this->getFieldConstraints($fieldKey, 'min');

        $fileCount = count($filesArray);

        if($fieldMaxItems !== null && $fileCount > $fieldMaxItems) {
          $this->validationResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::FileError->value,
              sprintf(
                __('The number of files uploaded for field "%s" exceeds the maximum allowed of %d.', 'modularity-frontend-form'),
                $fieldKey,
                $fieldMaxItems
              )
            )
          );
        }

        if($fieldMinItems !== null && $fileCount < $fieldMinItems) {
          $this->validationResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::FileError->value,
              sprintf(
                __('The number of files uploaded for field "%s" is less than the minimum required of %d.', 'modularity-frontend-form'),
                $fieldKey,
                $fieldMinItems
              )
            )
          );
        }

      }

      return $this->validationResult;
    }


    /**
     * Get field constraints from ACF
     *
     * @param string $fieldKey
     * @return array|null
     */
    private function getFieldConstraints(string $fieldKey, string $key): ?array
    {
      return $this->acfService->acfGetField($fieldKey)[$key] ?? null;
    }
}