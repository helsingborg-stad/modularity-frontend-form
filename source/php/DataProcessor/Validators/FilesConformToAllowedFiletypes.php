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

class FilesConformToAllowedFiletypes implements ValidatorInterface
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

      foreach($formattedFilesArray as $fieldKey => $filesArray) {

        $allowedMimeTypes = $this->getAllowedMimeTypes($fieldKey);

        foreach($filesArray as $fileProps) {
          $fileType   = $fileProps['type'] ?? '';
          $fileName   = $fileProps['name'] ?? '';
          $fileTmpPath = $fileProps['tmp_name'] ?? '';
          
          $postedFileMimeType = $fileType;
          $storedFileMimeType = $this->getMimeFromFile($fileTmpPath, $allowedMimeTypes);

          if($this->wpService->isWpError($storedFileMimeType)) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  $this->wpService->__('Unable to check type of file "%s". %s.', 'modularity-frontend-form'),
                  $fileName,
                  $storedFileMimeType->get_error_message()
                )
              )
            );
            continue;
          }

          if($postedFileMimeType !== $storedFileMimeType) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  $this->wpService->__('The file type is not allowed or communicated mime type (%s) does not match the actual file type (%s).', 'modularity-frontend-form'),
                  $postedFileMimeType ?? $this->wpService->__('unknown', 'modularity-frontend-form'),
                  $storedFileMimeType ?? $this->wpService->__('unknown', 'modularity-frontend-form')
                )
              )
            );
            continue;
          }
        }
      }
      return $this->validationResult; 
    }

    /**
     * Get mime type from file, only allowed mimes are allowed to be returned
     *
     * @param string $filePath
     * @param array|null $allowedMimes
     * @return string|null|WP_Error
     */
    private function getMimeFromFile(string $filePath, ?array $allowedMimes = null): null|string|WP_Error
    {
      if(!file_exists($filePath)) {
        return new WP_Error(
          RestApiResponseStatusEnums::FileError->value,
          $this->wpService->__('File does not exist', 'modularity-frontend-form')
        );
      }

      if(!is_readable($filePath)) {
        return new WP_Error(
          RestApiResponseStatusEnums::FileError->value,
          $this->wpService->__('Unable to read file', 'modularity-frontend-form')
        );
      }

      $fileMimeType = mime_content_type($filePath);
      if($allowedMimes && !in_array($fileMimeType, $allowedMimes)) {
        return null;
      }
      return $fileMimeType;
    }

    /**
     * Get allowed file types from ACF field
     *
     * @param string $fieldKey
     * @return array|null
     */
    private function getAllowedMimeTypes(string $fieldKey): ?array
    {
      $allowedMimes          = [];
      $allowedFileExtensions = $this->getAllowedFileExtensions($fieldKey);

      foreach ($allowedFileExtensions as $extension) {
        $mimeType = $this->getMimeTypeFromExtension($extension);
        if ($mimeType) {
          $allowedMimes[] = $mimeType;
        }
      }

      return $allowedMimes ?? null;
    }

    /**
     * Get allowed file extensions from ACF field
     *
     * @param string $fieldKey
     * @return array|null
     */
    private function getAllowedFileExtensions(string $fieldKey): ?array
    {
      $allowedTypes = $this->acfService->acfGetField($fieldKey)['mime_types'] ?? null;

      if (is_string($allowedTypes)) {
        $allowedTypes = array_filter(array_map('trim', explode(',', $allowedTypes)));
      }

      return is_array($allowedTypes) ? $allowedTypes : null;
    }

    /**
     * Get mime type from file extension
     *
     * @param string $extension
     * @return string|null
     */
    private function getMimeTypeFromExtension(string $extension): ?string
    {
      $extension      = strtolower(ltrim($extension, '.'));
      $mimeTypesList  = $this->wpService->wpGetMimeTypes();
    
      //Direct match
      if(isset($mimeTypesList[$extension])) {
        return $mimeTypesList[$extension];
      }

      //Search within compound extensions
      $map = [];
      foreach ($mimeTypesList as $extensions => $mime) {
          foreach (explode('|', $extensions) as $ext) {
              $map[strtolower(ltrim($ext, '.'))] = $mime;
          }
      }

      return $map[$extension] ?? null;
    }
}