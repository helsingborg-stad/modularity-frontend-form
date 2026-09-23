<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use InvalidArgumentException;

/** Protocol v4: native PHP-compatible fields and destination-named binary parts. */
final class MultipartFormDataEncoder
{
    public function encode(array|object $payload, array $files = [], int $fileLimit = 8388608): array
    {
        $boundary = '----WebhookMultipartBoundary' . bin2hex(random_bytes(16));
        $body = '';
        $fields = [];
        $this->flatten($payload, [], $fields);
        $fieldBytes = 0;
        foreach ($fields as $name => $value) {
            if (isset($files[$name])) {
                throw new InvalidArgumentException('An image destination cannot have both a value and an upload.');
            }
            $fieldBytes += strlen($value);
            if ($fieldBytes > 1_048_576) {
                throw new InvalidArgumentException('Multipart fields exceed the 1 MiB limit.');
            }
            $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"" . self::token($name) . "\"\r\n\r\n$value\r\n";
        }
        $total = 0;
        foreach ($files as $destination => $file) {
            if (!is_string($destination) || $destination === '' || !is_array($file)) {
                throw new InvalidArgumentException('Invalid image destination.');
            }
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
            $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"" . self::token($destination) . "\"; filename=\"$name\"\r\n"
                . "Content-Type: $type\r\n\r\n$bytes\r\n";
        }
        return ['body' => $body . "--$boundary--\r\n", 'contentType' => "multipart/form-data; boundary=$boundary"];
    }

    private function flatten(mixed $value, array $path, array &$fields): void
    {
        if ($value === null) {
            throw new InvalidArgumentException('Multipart cannot represent an explicit null value.');
        }
        if (is_array($value) || is_object($value)) {
            if ($value === []) {
                throw new InvalidArgumentException('Multipart cannot represent an empty array or object.');
            }
            foreach ($value as $key => $child) {
                if (!is_int($key) && (!is_string($key) || $key === '' || strpbrk($key, "[]\0") !== false)) {
                    throw new InvalidArgumentException('Multipart field names are invalid.');
                }
                $this->flatten($child, [...$path, (string) $key], $fields);
            }
            return;
        }
        if ($path === [] || !is_scalar($value)) {
            throw new InvalidArgumentException('Multipart values must be named scalars.');
        }
        $name = array_shift($path);
        foreach ($path as $segment) { $name .= "[$segment]"; }
        if (isset($fields[$name])) {
            throw new InvalidArgumentException('Multipart field names must be unique.');
        }
        $fields[$name] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
    }

    private static function token(string $value): string
    {
        return str_replace(["\r", "\n", '"', '\\'], ['', '', '%22', '%5C'], $value);
    }
}
