<?php

namespace ModularityFrontendForm\DataProcessor\Validators\Result;

use WP_Error;

interface ValidationResultInterface
{
    /**
     * Check if the validation result is valid.
     *
     * @return bool True if valid, false otherwise.
     */
    public function getIsValid(): bool;

    /**
     * Get the validation errors.
     *
     * @return array An array of WP_Error objects or null if no errors.
     */
    public function getErrors(): ?array;

    /**
     * Add an error to the validation result.
     *
     * @param WP_Error $error The error to add.
     */
    public function setError(WP_Error $error);
}
