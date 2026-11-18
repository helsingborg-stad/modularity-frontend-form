<?php

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
use WP_Error;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use ModularityFrontendForm\Helper\FilesArrayFormatter;

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

        $results   = [];
        $errors    = [];
        $files     = (new FilesArrayFormatter($request, $this->config))->getFormatted() ?? [];

        foreach ($files as $fieldKey => $fieldFiles) {
            foreach ($fieldFiles as $fileArray) {

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

                $sideloadTmpName = $this->createSideloadableTmpFile($fileArray);
                if ($sideloadTmpName === null) {
                    $errors[$fieldKey][] = new WP_Error(
                      RestApiResponseStatusEnums::FileError->value,
                      $this->wpService->__('File upload error.', 'modularity-frontend-form')
                    );
                    continue;
                }
                $fileArray['tmp_name'] = $sideloadTmpName;

                $attachmentIdOrWpError = $this->wpService->mediaHandleSideload(
                  $fileArray,
                  $postId ?? 0
                );

                // Sideload consumes the file on success. Remove a failed sideload's copy.
                if (file_exists($sideloadTmpName)) {
                    unlink($sideloadTmpName);
                }

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
     * Create an extension-bearing copy for media_handle_sideload().
     *
     * PHP upload temp files are extensionless. Some upload stream wrappers
     * interpret such paths as directories during the sideload prefilter.
     */
    private function createSideloadableTmpFile(array $fileArray): ?string
    {
        $tmpName = $this->wpService->wpTempnam($fileArray['name']);

        if (empty($tmpName) || !copy($fileArray['tmp_name'], $tmpName)) {
            if (!empty($tmpName) && file_exists($tmpName)) {
                unlink($tmpName);
            }
            return null;
        }

        return $tmpName;
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
