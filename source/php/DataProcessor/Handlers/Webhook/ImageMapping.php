<?php

declare(strict_types=1);

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

use AcfService\AcfService;
use InvalidArgumentException;
use stdClass;

/** One mapping plan selects snapshots and places their references after ordinary hydration. */
final class ImageMapping
{
    private array $fields = [];
    private array $destinations = [];
    private array $sources = [];
    private array $parentIds = [];

    public function __construct(private string $template, array $fieldKeys, AcfService $acf)
    {
        foreach ($fieldKeys as $key) {
            $field = $acf->getFieldObject($key);
            if (is_array($field)) {
                $this->parentIds[] = $field['key'];
                if (isset($field['ID'])) {
                    $this->parentIds[] = (string) $field['ID'];
                }
                $this->fields[$key] = $field;
                $this->fields[$field['name'] ?? $key] = $field;
            }
        }
        $decoded = json_decode($template);
        if (!$decoded instanceof stdClass) {
            throw new InvalidArgumentException('Multipart requires a JSON object template.');
        }
        $this->inspect($decoded);
    }

    public function sources(): array
    {
        return $this->sources;
    }

    private function inspect(mixed $value, array $path = [], bool $inList = false): void
    {
        if ($value instanceof stdClass && count((array) $value) === 2
            && isset($value->{'$optional'}) && is_string($value->{'$optional'}) && property_exists($value, '$value')) {
            $this->inspect($value->{'$value'}, $path, $inList);
            return;
        }
        if (is_object($value) || is_array($value)) {
            foreach ($value as $key => $child) {
                $this->inspect($child, [...$path, $key], $inList || is_array($value));
            }
            return;
        }
        if (!is_string($value)) {
            return;
        }
        self::rejectReferences($value);
        preg_match_all('/\{\{\s*([^{}]*?)\s*\}\}/', $value, $matches);
        foreach ($matches[1] as $source) {
            $source = trim($source);
            if ($source === '*') {
                foreach ($this->fields as $field) {
                    if (!$this->isTopLevelImage($field) && $this->containsUpload($field)) {
                        throw new InvalidArgumentException('Wildcard cannot include unsupported upload sources.');
                    }
                }
                continue;
            }
            $segments = explode('.', $source);
            $field = $this->fields[$segments[0]] ?? [];
            foreach (array_slice($segments, 1) as $segment) {
                if (ctype_digit($segment) || in_array($field['type'] ?? '', ['image', 'gallery', 'file'], true)) {
                    continue;
                }
                $children = $field['sub_fields'] ?? [];
                $field = [];
                foreach ($children as $child) {
                    if ($segment === ($child['name'] ?? null) || $segment === ($child['key'] ?? null)) {
                        $field = $child;
                        break;
                    }
                }
            }
            if (!$this->containsUpload($field)) {
                continue;
            }
            if (!$this->isTopLevelImage($field) || count($segments) !== 1
                || $inList || $path === [] || preg_match('/^\{\{\s*[^{}]*?\s*\}\}$/', $value) !== 1) {
                throw new InvalidArgumentException('Images require an unindexed top-level source and a complete object property.');
            }
            $key = $field['key'];
            $this->sources[$key] = $field;
            $this->destinations[] = [$path, $key];
        }
    }

    private function containsUpload(array $field): bool
    {
        if (in_array($field['type'] ?? '', ['image', 'gallery', 'file'], true)) {
            return true;
        }
        foreach ($field['sub_fields'] ?? [] as $child) {
            if ($this->containsUpload($child)) {
                return true;
            }
        }
        return false;
    }

    private function isTopLevelImage(array $field): bool
    {
        $parent = (string) ($field['parent'] ?? '');
        return ($field['type'] ?? '') === 'image' && !str_starts_with($parent, 'field_')
            && !in_array($parent, $this->parentIds, true);
    }

    public function hydrate(array $data, array $references): stdClass
    {
        // Never put generated references in submitted data or wildcard expansion.
        foreach ($this->fields as $alias => $field) {
            if (($field['type'] ?? '') === 'image') {
                unset($data[$alias]);
            }
        }
        $data['*'] = $data;
        $payload = json_decode((new JsonDotHydrator())->hydrate($this->template, $data));
        self::rejectReferences($payload);
        if (!$payload instanceof stdClass) {
            // Existing hydration represents an empty object as an empty array.
            if ($payload !== []) {
                throw new InvalidArgumentException('Multipart payload serialization failed.');
            }
            $payload = new stdClass();
        }
        foreach ($this->destinations as [$path, $source]) {
            $property = array_pop($path);
            $parent = $payload;
            foreach ($path as $segment) {
                $parent = $parent instanceof stdClass ? ($parent->{$segment} ?? null) : null;
            }
            if (!$parent instanceof stdClass || ($parent->{$property} ?? null) === null) {
                continue;
            }
            unset($parent->{$property});
            if (isset($references[$source])) {
                $reference = $references[$source];
                if (preg_match('/^\$file:[A-Za-z0-9_-]+$/D', $reference) !== 1) {
                    throw new InvalidArgumentException('Invalid generated image reference.');
                }
                $parent->{$property} = $reference;
            }
        }
        return $payload;
    }

    private static function rejectReferences(mixed $value): void
    {
        if (is_string($value) && str_starts_with($value, '$file:')) {
            throw new InvalidArgumentException('Reserved file references must originate from image mappings.');
        }
        if (is_array($value) || is_object($value)) {
            foreach ($value as $child) {
                self::rejectReferences($child);
            }
        }
    }
}
