<?php

namespace ModularityFrontendForm\DataProcessor;

use WP_REST_Request;
use WP_Error;

interface DataProcessorInterface
{
  /**
   * Process the data.
   *
   * @param array $data The data to process.
   * @return bool True if the submission succeeded, otherwise false.
   */
  public function process(array $data, WP_REST_Request $request): bool;

  /**
   * Get the first error.
   *
   * @return string|null The first error or null if no errors.
   */
  public function getFirstError(): ?WP_Error;

  /**
   * Get all errors.
   *
   * @return array|null An array of errors or null if no errors.
   */
  public function getErrors(): ?array;
}