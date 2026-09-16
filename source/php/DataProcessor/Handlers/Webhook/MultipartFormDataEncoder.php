<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use InvalidArgumentException;

/** Protocol v3: one JSON part and one binary per referenced key. */
final class MultipartFormDataEncoder
{
    public function encode(object $payload, array $files = [], int $fileLimit = 8388608): array
    {
        $json = json_encode($payload);
        if ($json === false || strlen($json) > 1048576) {
            throw new InvalidArgumentException('Multipart payload must be valid JSON no larger than 1 MiB.');
        }
        $keys = [];
        $collect = static function ($value) use (&$collect, &$keys): void {
            if (is_string($value) && str_starts_with($value, '$file:')) {
                if (preg_match('/^\$file:([A-Za-z0-9_-]+)$/D', $value, $match) !== 1) {
                    throw new InvalidArgumentException('Invalid image reference.');
                }
                $keys[$match[1]] = true;
            }
            if (is_array($value) || is_object($value)) {
                foreach ($value as $child) {
                    $collect($child);
                }
            }
        };
        $collect($payload);
        $boundary = '----WebhookMultipartBoundary' . bin2hex(random_bytes(16));
        $body = "--$boundary\r\nContent-Disposition: form-data; name=\"_acf_rest_payload\"\r\n"
            . "Content-Type: application/json\r\n\r\n$json\r\n";
        $total = 0;
        foreach (array_keys($keys) as $key) {
            $file = $files[$key] ?? [];
            $path = $file['tmp_name'] ?? '';
            $bytes = is_file($path) && is_readable($path) ? @file_get_contents($path, false, null, 0, $fileLimit + 1) : false;
            if ($bytes === false || $bytes === '') {
                throw new InvalidArgumentException('Referenced image snapshot is missing or unreadable.');
            }
            $total += strlen($bytes);
            if ($total > $fileLimit) {
                throw new InvalidArgumentException('Selected images exceed the webhook upload limit.');
            }
            $name = self::token((string) ($file['name'] ?? 'image'));
            $type = self::token((string) ($file['type'] ?? 'application/octet-stream'));
            $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"_acf_rest_files[$key]\"; filename=\"$name\"\r\n"
                . "Content-Type: $type\r\n\r\n$bytes\r\n";
        }
        return ['body' => $body . "--$boundary--\r\n", 'contentType' => "multipart/form-data; boundary=$boundary"];
    }

    private static function token(string $value): string
    {
        return str_replace(["\r", "\n", '"', '\\'], ['', '', '%22', '%5C'], $value);
    }
}
