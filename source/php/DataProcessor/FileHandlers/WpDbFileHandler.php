<?php

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
use WP_Error;

class WpDbFileHandler implements FileHandlerInterface {

    public function __construct(
      private Config $config, 
      private ModuleConfigInterface $moduleConfig,
      private WpService $wpService
    )
    {}

    public function handle(WP_REST_Request $request) {

        $files = $request->get_file_params()[$this->config->getFieldNamespace()] ?? null;
        
        $fieldKeys = array_keys($files['name'] ?? []);
        $results = [];

        foreach ($fieldKeys as $fieldKey) {
            // Support multiple files per field (array of files)
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
                    continue;
                }

                // Load WordPress media handling utilities
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';

                $attachment_id = media_handle_sideload($fileArray, 0);

                if (is_wp_error($attachment_id)) {
                    $results[$fieldKey][] = $attachment_id;
                } else {
                    $results[$fieldKey][] = [
                        'id'   => $attachment_id,
                        'url'  => wp_get_attachment_url($attachment_id),
                        'type' => get_post_mime_type($attachment_id),
                    ];
                }
            }
        }

        return $results;
    }
}