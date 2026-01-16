<?php

namespace ModularityFrontendForm\Helper;

use ModularityFrontendForm\Config\ConfigInterface;
use WP_REST_Request;

interface FilesArrayFormatterInterface
{
  /**
   * Returns files grouped by field and index: [field][index][fileProps]
   *
   * @return array|null
   */
  public function getFormatted(): ?array;
}