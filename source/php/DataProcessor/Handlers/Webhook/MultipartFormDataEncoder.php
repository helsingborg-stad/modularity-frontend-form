<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use InvalidArgumentException;

/**
 * Encodes a hydrated nested payload together with referenced file uploads into
 * a binary-safe multipart/form-data request body.
 *
 * Protocol:
 *  - Scalars and nested arrays/maps flatten into bracket parameter names
 *    (`field`, `field[child]`, `list[]`, `map[key]`).
 *  - A `null` value is not emitted as a normal parameter. Its bracket path is
 *    appended to the reserved `_acf_rest_nulls[]` list instead so the receiver
 *    can still distinguish "absent" from "explicit null".
 *  - A string that exactly matches `$file:<key>` with a safe key
 *    (`[A-Za-z0-9_-]+`) marks a file reference. The literal token is kept as a
 *    normal parameter so the receiver can map the field to its upload, while
 *    the file binary is emitted exactly once as `_acf_rest_files[<key>]`.
 *    Referencing the same key from several fields sends the binary only once.
 *    A `$file:` prefix without a safe key is an ordinary string.
 *  - Files present in the map but never referenced are not sent.
 *  - A referenced file that is absent from the map, or whose `tmp_name` is
 *    missing/unreadable, raises an InvalidArgumentException.
 *
 * The encoder is dependency-free and safe for PHP 8.2.
 */
final class MultipartFormDataEncoder
{
    public const FILE_REFERENCE_PREFIX = '$file:';

    public const NULLS_FIELD = '_acf_rest_nulls';

    public const FILES_FIELD = '_acf_rest_files';

    private string $boundary;

    public function __construct(?string $boundary = null)
    {
        if ($boundary !== null && $boundary === '') {
            throw new InvalidArgumentException('Multipart boundary must not be empty.');
        }

        $this->boundary = $boundary ?? self::generateBoundary();
    }

    /**
     * Encode the payload and referenced files into a multipart body.
     *
     * @param array<array-key, mixed> $payload Hydrated nested payload.
     * @param array<array-key, array{name?:string, type?:string, tmp_name?:string, size?:int}> $files
     *        Map of referenced file records keyed by file reference key.
     *
     * @return array{boundary:string, contentType:string, body:string}
     */
    public function encode(array $payload, array $files = []): array
    {
        /** @var array<int, string> $parts */
        $parts = [];
        /** @var array<array-key, true> $fileKeys */
        $fileKeys = [];
        /** @var array<int, string> $nullPaths */
        $nullPaths = [];

        $this->flatten($payload, '', $parts, $fileKeys, $nullPaths);

        foreach ($nullPaths as $path) {
            $parts[] = $this->scalarPart(self::NULLS_FIELD . '[]', $path);
        }

        foreach (array_keys($fileKeys) as $key) {
            $key = (string) $key;

            if (!array_key_exists($key, $files)) {
                throw new InvalidArgumentException(
                    sprintf('Referenced file "%s" is not present in the file map.', $key)
                );
            }

            $record = $files[$key];
            if (!is_array($record)) {
                throw new InvalidArgumentException(
                    sprintf('File record for "%s" must be an array.', $key)
                );
            }

            $parts[] = $this->filePart($key, $record);
        }

        $body = implode('', $parts) . '--' . $this->boundary . "--\r\n";

        return [
            'boundary' => $this->boundary,
            'contentType' => $this->contentType(),
            'body' => $body,
        ];
    }

    public function boundary(): string
    {
        return $this->boundary;
    }

    public function contentType(): string
    {
        return 'multipart/form-data; boundary=' . $this->boundary;
    }

    /**
     * @param array<int, string> $parts
     * @param array<array-key, true> $fileKeys
     * @param array<int, string> $nullPaths
     */
    private function flatten(
        mixed $value,
        string $path,
        array &$parts,
        array &$fileKeys,
        array &$nullPaths
    ): void {
        if ($value === null) {
            // Null is signalled out-of-band so it is not confused with an
            // empty string parameter.
            $nullPaths[] = $path;

            return;
        }

        if (is_array($value)) {
            foreach ($value as $key => $child) {
                if (is_int($key)) {
                    // List entries keep their bracket syntax.
                    $childPath = $path . '[]';
                } elseif ($path === '') {
                    // Root map keys are plain parameter names, not [key].
                    $childPath = (string) $key;
                } else {
                    $childPath = $path . '[' . $key . ']';
                }

                $this->flatten($child, $childPath, $parts, $fileKeys, $nullPaths);
            }

            return;
        }

        if (is_string($value) && self::isFileReference($value)) {
            $key = substr($value, strlen(self::FILE_REFERENCE_PREFIX));
            $fileKeys[$key] = true;
            // Keep the token itself: the receiver needs the field value to
            // know which upload the field points at.
            $parts[] = $this->scalarPart($path, $value);

            return;
        }

        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            $parts[] = $this->scalarPart($path, self::stringify($value));

            return;
        }

        throw new InvalidArgumentException(
            sprintf('Unsupported value of type "%s" at path "%s".', get_debug_type($value), $path)
        );
    }

    private function scalarPart(string $name, string $value): string
    {
        return '--' . $this->boundary . "\r\n"
            . 'Content-Disposition: form-data; name="' . self::sanitizeToken($name) . '"' . "\r\n"
            . "\r\n"
            . $value . "\r\n";
    }

    /**
     * @param array{name?:string, type?:string, tmp_name?:string, size?:int} $record
     */
    private function filePart(string $key, array $record): string
    {
        $tmpName = $record['tmp_name'] ?? null;
        if (!is_string($tmpName) || $tmpName === '' || !is_file($tmpName) || !is_readable($tmpName)) {
            throw new InvalidArgumentException(
                sprintf('Referenced file "%s" is missing or unreadable.', $key)
            );
        }

        $binary = @file_get_contents($tmpName);
        if ($binary === false) {
            throw new InvalidArgumentException(
                sprintf('Referenced file "%s" could not be read.', $key)
            );
        }

        $fileName = isset($record['name']) && is_string($record['name']) ? $record['name'] : '';
        $contentType = isset($record['type']) && is_string($record['type']) && $record['type'] !== ''
            ? $record['type']
            : 'application/octet-stream';

        $name = self::FILES_FIELD . '[' . $key . ']';

        return '--' . $this->boundary . "\r\n"
            . 'Content-Disposition: form-data; name="' . self::sanitizeToken($name) . '"'
            . '; filename="' . self::sanitizeToken($fileName) . '"' . "\r\n"
            . 'Content-Type: ' . self::sanitizeToken($contentType) . "\r\n"
            . "\r\n"
            . $binary . "\r\n";
    }

    /**
     * A file reference is only recognised when the key is non-empty and safe
     * (`[A-Za-z0-9_-]+`). Values such as `$file:` or `$file:foo!` stay ordinary
     * scalar strings.
     */
    private static function isFileReference(string $value): bool
    {
        return preg_match('/^' . preg_quote(self::FILE_REFERENCE_PREFIX, '/') . '[A-Za-z0-9_-]+$/', $value) === 1;
    }

    private static function stringify(bool|int|float|string $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private static function sanitizeToken(string $value): string
    {
        return str_replace(["\r", "\n", '"'], ['', '', '%22'], $value);
    }

    private static function generateBoundary(): string
    {
        return '----WebhookMultipartBoundary' . bin2hex(random_bytes(16));
    }
}
