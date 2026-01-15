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
          $fileType = $fileProps['type'] ?? '';
          $fileName = $fileProps['name'] ?? '';
          
          $postedFileMimeType = $fileType;
          $storedFileMimeType = $this->getMimeFromFile($fileProps['tmp_name'], $allowedMimeTypes);

          if($postedFileMimeType !== $storedFileMimeType) {
            $this->validationResult->setError(
              new WP_Error(
                RestApiResponseStatusEnums::FileError->value,
                sprintf(
                  __('The file type is not allowed or communicated mime type does not match the actual file type.', 'modularity-frontend-form'),
                  $postedFileMimeType
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
     * Get mime type from file
     *
     * @param string $filePath
     * @param array|null $allowedMimes
     * @return string|null
     */
    private function getMimeFromFile(string $filePath, ?array $allowedMimes = null): ?string
    {
      $fileInfo = $this->wpService->wpCheckFiletype(
        $filePath,
        $allowedMimes
      );

      if(array_filter($fileInfo) === []) {
        return null;
      }

      return $fileInfo['type'] ?? null;
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
      $allowedTypes = $this->acfService->acfGetField($fieldKey)['allowed_types'] ?? null;

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