<?php

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
use WP_Error;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;

class WpDbFileHandler implements FileHandlerInterface {

    public function __construct(
      private Config $config, 
      private ModuleConfigInterface $moduleConfig,
      private WpService $wpService
    )
    {}

    public function handle(WP_REST_Request $request, ?int $postId = null) {

        $files = $request->get_file_params()[$this->config->getFieldNamespace()] ?? null;
        $fieldKeys = array_keys($files['name'] ?? []);
        $results = [];
        $errors = [];

        foreach ($fieldKeys as $fieldKey) {
            $fileCount = is_array($files['name'][$fieldKey]) ? count($files['name'][$fieldKey]) : 0;

            for ($i = 0; $i < $fileCount; $i++) {
                $fileArray = [
                    'name'     => $files['name'][$fieldKey][$i] ?? '',
                    'type'     => $files['type'][$fieldKey][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$fieldKey][$i] ?? '',
                    'error'    => $files['error'][$fieldKey][$i] ?? 4,
                    'size'     => $files['size'][$fieldKey][$i] ?? 0,
                ];

                if (empty($fileArray['name']) || $fileArray['error'] !== UPLOAD_ERR_OK) {
                    $errors[$fieldKey][] = new WP_Error(
                      RestApiResponseStatusEnums::FileError->value, 
                      $this->wpService->__('File upload error.', 'modularity-frontend-form')
                    );
                    continue;
                }

                // Load WordPress media handling utilities
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attachmentIdOrWpError = $this->wpService->mediaHandleSideload(
                  $fileArray,
                  $postId ?? 0
                );

                if ($this->wpService->isWpError($attachmentIdOrWpError)) {
                    $errors[$fieldKey][] = $attachmentIdOrWpError;
                } else {
                    $results[$fieldKey][] = [
                        'id'   => $attachmentIdOrWpError,
                        'url'  => wp_get_attachment_url($attachmentIdOrWpError),
                        'type' => get_post_mime_type($attachmentIdOrWpError),
                    ];
                }
            }
        }

        if(!empty($errors)) {
            return new WP_Error(
              RestApiResponseStatusEnums::FileError->value, 
              $this->wpService->__('One or more files failed to upload.', 'modularity-frontend-form'),
              [
                'errors' => $errors
              ]
          );
        }

        return $results;
    }
}