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

class FilesIntegrityCheck implements ValidatorInterface
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

        //No files to validate
        if (!$formattedFilesArray = $filesArrayFormatter->getFormatted()) {
            return $this->validationResult;
        }

      foreach ($formattedFilesArray as $filesArray) {
        foreach ($filesArray as $fileProps) {

          // No temp file associated with the upload
          if (!isset($fileProps['tmp_name']) || !is_file($fileProps['tmp_name'])) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  $this->wpService->__('The file "%s" could not be uploaded [unable to find the file].', 'modularity-frontend-form'),
                  $fileProps['name'],
                )
              )
            );
            continue;
          }

          $fileSizeOnDisk = filesize($fileProps['tmp_name']);

          // Could not be found or accessed
          if ($fileSizeOnDisk === false) {
             $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  $this->wpService->__('The file "%s" could not be uploaded [unable to access the file].', 'modularity-frontend-form'),
                  $fileProps['name'],
                )
              )
            );
            continue;
          }

          // File size on disk does not match the reported size
          if ($fileSizeOnDisk != $fileProps['size']) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  $this->wpService->__('The file "%s" (%d) does not match the expected filesize of %d. The file might be corrupted or incomplete.', 'modularity-frontend-form'),
                  $fileProps['name'],
                  $fileSizeOnDisk,
                  $fileProps['size']
                )
              )
            );
          }
        }
      }

      return $this->validationResult;
    }
}