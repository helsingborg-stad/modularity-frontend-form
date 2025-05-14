<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Result;

use WP_Error;

interface HandlerResultInterface
{
    /**
     * Check if the handler did ok.
     *
     * @return bool True if valid, false otherwise.
     */
    public function isOk(): bool;

    /**
     * Get the handler errors.
     *
     * @return array An array of WP_Error objects or null if no errors.
     */
    public function getErrors(): ?array;

    /**
     * Add an error that occurs inside a handler.
     *
     * @param WP_Error $error The error to add.
     */
    public function setError(WP_Error $error);
}
