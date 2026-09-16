<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\AcfService;
use Throwable;

/** Owns only explicitly mapped top-level images, independently of original uploads. */
final class UploadedFileSnapshots
{
    private array $files = [];
    private array $references = [];
    private ?ImageMapping $mapping = null;

    public function __construct(mixed $fileParams, AcfService $acf, mixed $template, array $fieldKeys)
    {
        try {
            $mapping = new ImageMapping($template, $fieldKeys, $acf);
            foreach ($mapping->sources() as $key => $field) {
                $this->capture($fileParams, $key, $field['name'] ?? $key);
            }
            $this->mapping = $mapping;
        } catch (Throwable) {
            $this->cleanup();
        }
        if ($this->files !== []) {
            register_shutdown_function(fn() => $this->cleanup());
        }
    }

    private function capture(array $params, string $fieldKey, string $name): void
    {
        $inputKey = array_key_exists($fieldKey, $params['name'] ?? []) ? $fieldKey : $name;
        if (!array_key_exists($inputKey, $params['name'] ?? [])) {
            return;
        }
        $record = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $attribute) {
            $value = $params[$attribute][$inputKey] ?? null;
            if (is_array($value)) {
                if (count($value) !== 1 || !array_key_exists(0, $value)) {
                    throw new \InvalidArgumentException('Only one upload per image is supported.');
                }
                $value = $value[0];
            }
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException('Invalid upload structure.');
            }
            $record[$attribute] = $value;
        }
        if ($record['error'] === UPLOAD_ERR_NO_FILE && $record['name'] === '') {
            return;
        }
        if ($record['error'] !== UPLOAD_ERR_OK || !is_string($record['tmp_name'])
            || $record['name'] === '' || !is_file($record['tmp_name']) || !is_readable($record['tmp_name'])) {
            throw new \InvalidArgumentException('Selected upload is incomplete or unreadable.');
        }
        $path = tempnam(sys_get_temp_dir(), 'mff-webhook-');
        if ($path === false) {
            throw new \RuntimeException('Snapshot creation failed.');
        }
        $key = 'file_' . count($this->files);
        $this->files[$key] = [...$record, 'tmp_name' => $path];
        if (!@copy($record['tmp_name'], $path) || !@chmod($path, 0600)) {
            throw new \RuntimeException('Snapshot copy failed.');
        }
        $this->references[$fieldKey] = '$file:' . $key;
    }

    public function payload(array $data): \stdClass
    {
        if ($this->mapping === null) {
            throw new \InvalidArgumentException('Webhook image preparation failed. The submission was not sent.');
        }
        return $this->mapping->hydrate($data, $this->references);
    }

    public function getFileMap(): array
    {
        return $this->files;
    }

    public function cleanup(): bool
    {
        foreach ($this->files as $key => $file) {
            $path = $file['tmp_name'];
            if ((!file_exists($path) && !is_link($path)) || @unlink($path)) {
                unset($this->files[$key]);
            }
        }
        $this->mapping = null;
        $this->references = [];
        return $this->files === [];
    }
}
