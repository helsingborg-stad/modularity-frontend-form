<?php

// Test-only endpoint. PHP, not this script, parses multipart names and bytes.
$files = [];
foreach ($_FILES['_acf_rest_files']['tmp_name'] ?? [] as $key => $path) {
    $files[$key] = [
        'name' => $_FILES['_acf_rest_files']['name'][$key],
        'error' => $_FILES['_acf_rest_files']['error'][$key],
        'bytes' => base64_encode(file_get_contents($path)),
    ];
}
header('Content-Type: application/json');
echo json_encode(['pid' => getmypid(), 'post' => $_POST, 'fileFields' => array_keys($_FILES), 'files' => $files,
    'version' => $_SERVER['HTTP_X_ACF_REST_UPLOAD_VERSION'] ?? null]);
