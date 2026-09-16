<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use PHPUnit\Framework\TestCase;

abstract class HandlerTestCase extends TestCase
{
    protected function setUp(): void
    {
        // The installed WordPress stubs discard constructor arguments. Record the
        // public WP_Error boundary so message/data assertions cannot pass vacuously.
        \Patchwork\redefine('WP_Error::__construct', function ($code = '', $message = '', $data = '') {
            $this->errors[$code] = [$message];
            $this->error_data[$code] = $data;
        });
        \Patchwork\redefine('WP_Error::get_error_code', function () { return array_key_first($this->errors); });
        \Patchwork\redefine('WP_Error::get_error_codes', function () { return array_keys($this->errors); });
        \Patchwork\redefine('WP_Error::get_error_messages', function ($code) { return $this->errors[$code]; });
        \Patchwork\redefine('WP_Error::get_error_data', function ($code) { return $this->error_data[$code]; });
    }

    protected function tearDown(): void
    {
        \Patchwork\restoreAll();
    }
}
