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

    /**
     * Handle file uploads from the request and attach them to the specified post.
     *
     * @param WP_REST_Request $request The REST request containing file uploads.
     * @param int|null $postId The ID of the post to attach files to.
     * @return WP_Error|array An array of uploaded file info or a WP_Error on failure.
     */
    public function handle(WP_REST_Request $request, ?int $postId = null): WP_Error|array
    {

        $files     = $request->get_file_params()[$this->config->getFieldNamespace()] ?? null;
        $fieldKeys = array_keys($files['name'] ?? []);
        $results   = [];
        $errors    = [];
        $files     = $this->filterEmptyFieldKeys($fieldKeys, $files);

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

    /**
     * Filter out field keys that have no files uploaded.
     *
     * @param array $fieldKeys The field keys to check.
     * @return array The filtered field keys.
     */
    private function filterEmptyFieldKeys(array $fieldKeys, array $files): array {
        foreach ($fieldKeys as $fieldKey) {
            $tmpNames = $files['tmp_name'][$fieldKey] ?? [];
            if (!is_array($tmpNames)) {
              $tmpNames = [$tmpNames];
            }
            if (count(array_filter($tmpNames, fn($v) => !empty($v))) === 0) {
              foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attr) {
                  unset($files[$attr][$fieldKey]);
              }
            }
        }
        return $files;
    }

    /**
     * Delete files that have been removed from the submission
     * TODO: Implement.
     *
     * @param array $existingFileIds The existing file IDs
     * @param array $newFileIds The new file IDs
     * 
     * @return void
     */
    private function deleteRemovedFiles(array $existingFileIds, array $newFileIds): void {
        $fileIdsToDelete = array_diff($existingFileIds, $newFileIds);

        foreach ($fileIdsToDelete as $fileId) {
            $this->wpService->wpDeleteAttachment($fileId, true);
        }
    }
}