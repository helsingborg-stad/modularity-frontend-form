<?php

namespace ModularityFrontendForm\Validators\Result;

use WP_Error;

interface ValidationResultInterface
{
    public function getIsValid(): bool;
    public function getErrors(): array;
    public function setError(WP_Error $error);
}

