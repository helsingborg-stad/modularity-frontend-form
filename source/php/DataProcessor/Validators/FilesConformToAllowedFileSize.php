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
use ModularityFrontendForm\Helper\FilesArrayFormatterInterface;
use ModularityFrontendForm\Helper\FilesArrayFormatter;

class FilesConformToAllowedFileSize implements ValidatorInterface
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
      if ($filesArrayFormatter === null) {
        $filesArrayFormatter = new FilesArrayFormatter($request, $this->config);
      }

      if (! $formattedFilesArray = $filesArrayFormatter->getFormatted()) {
        return $this->validationResult;
      }

      foreach ($formattedFilesArray as $fieldKey => $filesArray) {
        $fieldMaxSize = $this->getFieldConstraints($fieldKey, 'max_size');

        if (! $fieldMaxSize) {
          $fieldMaxSize = $this->wpService->wpMaxUploadSize() / 1024;
        }

        $maxFileSizeInBytes  = $fieldMaxSize * 1024;
        $maxFileSizeReadable = size_format($maxFileSizeInBytes);

        foreach ($filesArray as $fileProps) {
          if (isset($fileProps['size']) && $fileProps['size'] > $maxFileSizeInBytes) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  __('The file "%s" exceeds the maximum allowed file size of %s.', 'modularity-frontend-form'),
                  $fileProps['name'],
                  $maxFileSizeReadable
                )
              )
            );
          }
        }
      }

      return $this->validationResult;
    }

    /**
     * Get field constraints from ACF
     *
     * @param string $fieldKey
     * @return mixed
     */
    private function getFieldConstraints(string $fieldKey, string $key): mixed
    {
      return $this->acfService->acfGetField($fieldKey)[$key] ?? null;
    }
}