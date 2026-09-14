<?php

// WordPress result-format constants are needed when PHPUnit mocks WpService.
foreach (['OBJECT', 'OBJECT_K', 'ARRAY_A', 'ARRAY_N'] as $format) {
    if (!defined($format)) {
        define($format, $format);
    }
}

include_once __DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php';
require_once __DIR__ . '/vendor/autoload.php';
