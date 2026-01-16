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
    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
      
      $filesArrayFormatter = new FilesArrayFormatter($request, $this->config);

      if (!$formattedFilesArray = $filesArrayFormatter->getFormatted()) {
        return $this->validationResult;
      }

      $checkedFieldKeys = [];

      foreach($formattedFilesArray as $fieldKey => $filesArray) {

        if (in_array($fieldKey, $checkedFieldKeys)) {
          continue;
        }

        $fieldMaxItems = $this->getFieldConstraints($fieldKey, 'max');
        $fieldMinItems = $this->getFieldConstraints($fieldKey, 'min');

        $fileCount = count($filesArray);

        if ($fieldMaxItems !== null && is_numeric($fieldMaxItems) && $fileCount > $fieldMaxItems) {

          $fieldLabel = $this->acfService->acfGetField($fieldKey)['label'] ?? $fieldKey;

          $this->validationResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::FileError->value,
              sprintf(
                __('The number of files uploaded for field "%s" exceeds the maximum allowed of %d.', 'modularity-frontend-form'),
                $fieldLabel,
                $fieldMaxItems
              )
            )
          );
        }

        if ($fieldMinItems !== null && is_numeric($fieldMinItems) && $fileCount < $fieldMinItems) {

          $fieldLabel = $this->acfService->acfGetField($fieldKey)['label'] ?? $fieldKey;

          $this->validationResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::FileError->value,
              sprintf(
                __('The number of files uploaded for field "%s" is less than the minimum required of %d.', 'modularity-frontend-form'),
                $fieldLabel,
                $fieldMinItems
              )
            )
          );
        }

      }

      return $this->validationResult;
    }


    /**
     * Get field constraint value from ACF
     *
     * @param string $fieldKey
     * @param string $key
     * @return int|null
     */
    private function getFieldConstraints(string $fieldKey, string $key): mixed
    {
      return $this->acfService->acfGetField($fieldKey)[$key] ?? null;
    }
}