<?php

namespace ModularityFrontendForm\Helper;

use Adbar\Dot;

class JsonDotHydrator implements Hydrator
{
    public function hydrate(string $template, array $data): string
    {
        $decoded = json_decode($template, true);

        if ($decoded === null) {
            return $template;
        }

        return json_encode($this->replace($decoded, new Dot($data)));
    }

    private function replace(mixed $data, Dot $values): mixed
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace($value, $values);
            }
            return $data;
        }

        if (is_string($data)) {
            if (preg_match('/^\{\{\s*([^{}]*?)\s*\}\}$/', $data, $match)) {
                return $this->resolveValue($match[1], $values);
            }

            return preg_replace_callback(
                '/\{\{\s*(.*?)\s*\}\}/',
                fn($m) => is_scalar($v = $this->resolveValue($m[1], $values))
                    ? (string) $v
                    : json_encode($v),
                $data
            );
        }

        return $data;
    }

    private function resolveValue(string $key, Dot $values): mixed
    {
        $sentinel = new \stdClass();
        $resolved = $values->get($key, $sentinel);

        if ($resolved === $sentinel || $resolved === null) {
            return '';
        }

        return $resolved;
    }
}
