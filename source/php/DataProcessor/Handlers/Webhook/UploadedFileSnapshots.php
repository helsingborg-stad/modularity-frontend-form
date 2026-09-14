<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\AcfService;

/**
 * Immutable-per-request snapshot of the uploaded files belonging to a form
 * submission.
 *
 * The snapshot is created before handlers run so that a Webhook handler can
 * still read the uploaded binaries even if an earlier handler (for example the
 * database handler) consumes or moves the original temporary uploads.
 *
 * Every valid upload is copied to its own file inside sys_get_temp_dir() and
 * exposed through a deterministic key (file_0, file_1, ...). Each record keeps
 * the originating ACF field key, its converted field name and the index inside
 * an indexed (gallery) list.
 */
final class UploadedFileSnapshots
{
    public const FILE_KEY_PREFIX = 'file_';

    /**
     * Stable field key used for the scalar single-file layout, where the
     * parallel attributes (`name`, `type`, `tmp_name`, ...) are not nested.
     */
    private const SCALAR_FIELD_KEY = 'file';

    /** @var array<int, array<string, mixed>> */
    private array $snapshots = [];

    /** @var array<int, string> */
    private array $snapshotPaths = [];

    private int $nextIndex = 0;

    private bool $shutdownCleanupRegistered = false;

    /**
     * @param array<array-key, mixed> $fileParams The file params collected from
     *        `WP_REST_Request::get_file_params()`, already scoped to the form
     *        field namespace.
     */
    public function __construct(
        array $fileParams,
        private ?AcfService $acfService = null
    ) {
        $this->capture($fileParams);
        $this->registerShutdownCleanup();
    }

    /**
     * @param array<array-key, mixed> $fileParams
     */
    private function capture(array $fileParams): void
    {
        $names = $fileParams['name'] ?? null;

        if (!is_array($names)) {
            if (!is_string($names) && !is_int($names) && !is_float($names)) {
                return;
            }

            // Scalar single-file layout: wrap the parallel attributes so the
            // regular array walk can treat them as a leaf under a stable key.
            $fileParams = $this->normalizeScalarLayout($fileParams);
            $names      = $fileParams['name'] ?? null;
        }

        if (!is_array($names)) {
            return;
        }

        $this->walk($names, $fileParams, []);
    }

    /**
     * Wrap the parallel file attributes of the scalar layout under a stable
     * field key.
     *
     * @param array<array-key, mixed> $fileParams
     * @return array<array-key, mixed>
     */
    private function normalizeScalarLayout(array $fileParams): array
    {
        $normalized = [];

        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attribute) {
            if (!array_key_exists($attribute, $fileParams)) {
                continue;
            }

            $value = $fileParams[$attribute];
            $normalized[$attribute] = is_array($value)
                ? $value
                : [self::SCALAR_FIELD_KEY => $value];
        }

        return $normalized;
    }

    /**
     * Snapshots are usually removed by the WebHook handler. If that handler
     * never runs the shutdown guard still removes them at the end of the
     * request. `cleanup()` is idempotent, so an explicit call is harmless.
     */
    private function registerShutdownCleanup(): void
    {
        if ($this->snapshots === [] || $this->shutdownCleanupRegistered) {
            return;
        }

        $this->shutdownCleanupRegistered = true;

        register_shutdown_function(function (): void {
            $this->cleanup();
        });
    }

    /**
     * @param array<array-key, mixed> $names
     * @param array<array-key, mixed> $files
     * @param array<int, string|int>  $path
     */
    private function walk(mixed $names, array $files, array $path): void
    {
        if (is_array($names)) {
            foreach ($names as $key => $child) {
                $this->walk($child, $files, [...$path, $key]);
            }

            return;
        }

        $this->captureLeaf($files, $path, $names);
    }

    /**
     * @param array<array-key, mixed> $files
     * @param array<int, string|int>  $path
     */
    private function captureLeaf(array $files, array $path, mixed $name): void
    {
        if ($path === [] || !is_string($name) || trim($name) === '') {
            return;
        }

        $error = $this->pluck($files, 'error', $path, UPLOAD_ERR_NO_FILE);
        if ((int) $error !== UPLOAD_ERR_OK) {
            return;
        }

        $tmpName = $this->pluck($files, 'tmp_name', $path);
        if (!is_string($tmpName) || $tmpName === '' || !is_file($tmpName) || !is_readable($tmpName)) {
            return;
        }

        $snapshotPath = $this->snapshotFile($tmpName);
        if ($snapshotPath === null) {
            return;
        }

        $fieldKey = $this->resolveFieldKey($path);
        $key      = self::FILE_KEY_PREFIX . $this->nextIndex;

        $this->snapshots[] = [
            'key'       => $key,
            'fieldKey'  => $fieldKey,
            'fieldName' => $this->resolveFieldName($fieldKey),
            'index'     => $this->resolveIndex($path, $fieldKey),
            'path'      => $path,
            'name'      => $name,
            'type'      => (string) $this->pluck($files, 'type', $path, ''),
            'size'      => (int) $this->pluck($files, 'size', $path, 0),
            'tmp_name'  => $snapshotPath,
        ];
        $this->snapshotPaths[] = $snapshotPath;

        $this->nextIndex++;
    }

    /**
     * Read a parallel attribute (type, tmp_name, ...) at the given path.
     *
     * @param array<array-key, mixed> $files
     * @param array<int, string|int>  $path
     */
    private function pluck(array $files, string $attribute, array $path, mixed $default = null): mixed
    {
        $value = $files[$attribute] ?? null;

        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * @param array<int, string|int> $path
     */
    private function resolveFieldKey(array $path): string
    {
        $fieldKeys = array_values(array_filter(
            $path,
            static fn($segment): bool => is_string($segment) && str_starts_with($segment, 'field_')
        ));

        if ($fieldKeys !== []) {
            return (string) end($fieldKeys);
        }

        foreach ($path as $segment) {
            if (is_string($segment)) {
                return $segment;
            }
        }

        return '';
    }

    /**
     * @param array<int, string|int> $path
     */
    private function resolveIndex(array $path, string $fieldKey): ?int
    {
        $position = array_search($fieldKey, $path, true);

        if ($position === false) {
            $integers = array_values(array_filter($path, static fn($segment): bool => is_int($segment)));

            return $integers === [] ? null : (int) end($integers);
        }

        // Gallery shape: <field_key>[<index>].
        $next = $path[$position + 1] ?? null;
        if (is_int($next)) {
            return $next;
        }

        // Repeater shape: <repeater>[<row>][<field_key>].
        for ($i = $position - 1; $i >= 0; $i--) {
            if (is_int($path[$i])) {
                return $path[$i];
            }
        }

        return null;
    }

    private function resolveFieldName(string $fieldKey): string
    {
        if ($this->acfService === null || $fieldKey === '') {
            return $fieldKey;
        }

        $fieldObject = $this->acfService->getFieldObject($fieldKey);

        return is_array($fieldObject) && is_string($fieldObject['name'] ?? null) && $fieldObject['name'] !== ''
            ? $fieldObject['name']
            : $fieldKey;
    }

    private function snapshotFile(string $tmpName): ?string
    {
        $snapshot = tempnam(sys_get_temp_dir(), 'mff-webhook-');
        if ($snapshot === false) {
            return null;
        }

        if (!@copy($tmpName, $snapshot)) {
            @unlink($snapshot);

            return null;
        }

        @chmod($snapshot, 0600);

        return $snapshot;
    }

    /**
     * File records keyed by their deterministic file key, suitable for
     * MultipartFormDataEncoder::encode().
     *
     * @return array<string, array{name:string, type:string, tmp_name:string, size:int}>
     */
    public function getFileMap(): array
    {
        $map = [];

        foreach ($this->snapshots as $snapshot) {
            $map[$snapshot['key']] = [
                'name'     => (string) $snapshot['name'],
                'type'     => (string) $snapshot['type'],
                'tmp_name' => (string) $snapshot['tmp_name'],
                'size'     => (int) $snapshot['size'],
            ];
        }

        return $map;
    }

    /**
     * Hydration references keyed by both the ACF field key and the converted
     * field name. Singleton uploads map to a single `$file:<key>` string and
     * indexed (gallery) uploads map to an ordered list of references.
     *
     * @return array<string, string|array<int, string>>
     */
    public function getReferences(): array
    {
        return array_merge(
            $this->buildReferences(static fn(array $snapshot): string => (string) $snapshot['fieldKey']),
            $this->buildReferences(static fn(array $snapshot): string => (string) $snapshot['fieldName'])
        );
    }

    /**
     * @param callable(array<string, mixed>): string $keySelector
     * @return array<string, string|array<int, string>>
     */
    private function buildReferences(callable $keySelector): array
    {
        /** @var array<string, array<int, array<string, mixed>>> $grouped */
        $grouped = [];

        foreach ($this->snapshots as $snapshot) {
            $key = $keySelector($snapshot);
            if ($key === '') {
                continue;
            }

            $grouped[$key][] = $snapshot;
        }

        $references = [];

        foreach ($grouped as $key => $snapshots) {
            $hasIndex = false;
            foreach ($snapshots as $snapshot) {
                if ($snapshot['index'] !== null) {
                    $hasIndex = true;
                    break;
                }
            }

            if (!$hasIndex) {
                $references[$key] = MultipartFormDataEncoder::FILE_REFERENCE_PREFIX . $snapshots[0]['key'];
                continue;
            }

            /** @var array<int, string> $ordered */
            $ordered = [];
            foreach ($snapshots as $snapshot) {
                $ordered[$snapshot['index']] = MultipartFormDataEncoder::FILE_REFERENCE_PREFIX . $snapshot['key'];
            }

            ksort($ordered);
            $references[$key] = array_values($ordered);
        }

        return $references;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSnapshots(): array
    {
        return $this->snapshots;
    }

    public function hasFiles(): bool
    {
        return $this->snapshots !== [];
    }

    /**
     * Remove every snapshot from disk. Safe to call more than once.
     */
    public function cleanup(): void
    {
        foreach ($this->snapshotPaths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $this->snapshotPaths = [];
        $this->snapshots    = [];
        $this->nextIndex    = 0;
    }
}
