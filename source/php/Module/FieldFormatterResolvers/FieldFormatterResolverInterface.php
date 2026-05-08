<?php

namespace ModularityFrontendForm\Module\FieldFormatterResolvers;

interface FieldFormatterResolverInterface
{
    /**
     * Format the given field data
     *
     * @param string $name The field data to format
     * @return array The formatted field data
     */
    public function resolve(string $name): array|null;
}