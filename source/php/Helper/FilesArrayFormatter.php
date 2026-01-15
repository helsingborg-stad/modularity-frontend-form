<?php

namespace ModularityFrontendForm\Helper;

use ModularityFrontendForm\Config\ConfigInterface;
use WP_REST_Request;

class FilesArrayFormatter implements FilesArrayFormatterInterface
{
  private $request;
  private $config;

  public function __construct(WP_REST_Request $request, ConfigInterface $config)
  {
    $this->request = $request;
    $this->config = $config;
  }

  /**
   * Extract files from the request based on the config
   *
   * @return array|null
   */
  private function getFilesFromRequest(): ?array
  {
    $namespace = $this->config->getFieldNamespace();
    $files = $this->request->get_file_params()[$namespace] ?? [];
    if (empty($files) || !is_array($files)) {
      return null;
    }
    return $files;
  }

  /**
   * Returns files grouped by field and index: [field][index][fileProps]
   *
   * @return array|null
   */
  public function getFormatted(): ?array
  {
    $files = $this->getFilesFromRequest();
    if (!$files || !isset($files['name']) || !is_array($files['name'])) {
      return null;
    }

    $result = [];
    foreach ($files['name'] as $field => $names) {
      if (is_array($names)) {
        // Multiple files for this field
        foreach ($names as $i => $name) {
          $result[$field][$i] = [
            'name'     => $name,
            'type'     => $files['type'][$field][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$field][$i] ?? '',
            'error'    => $files['error'][$field][$i] ?? 4,
            'size'     => $files['size'][$field][$i] ?? 0,
          ];
        }
      } else {
        // Single file upload for this field
        $result[$field][] = [
          'name'     => $files['name'][$field] ?? '',
          'type'     => $files['type'][$field] ?? '',
          'tmp_name' => $files['tmp_name'][$field] ?? '',
          'error'    => $files['error'][$field] ?? 4,
          'size'     => $files['size'][$field] ?? 0,
        ];
      }
    }
    return $result;
  }
}