<?php

namespace ModularityFrontendForm\DataProcessor\FileHandlers\Response;

interface FileHandlerResponseInterface
{
  /**
   * Get the response data as an array.
   *
   * @return array
   */
  public function get(): array;

  /**
   * Set the status of the response.
   *
   * @param string $status
   * @return void
   */
  public function setStatus(string $status): void;

  /**
   * Set the message of the response.
   *
   * @param string $message
   * @return void
   */
  public function setMessage(string $message): void;

  /**
   * Add a file entry to the response data.
   *
   * @param int $id
   * @param string $url
   * @param string $path
   * @return void
   */
  public function add(int $id, string $url, string $path): void;
}