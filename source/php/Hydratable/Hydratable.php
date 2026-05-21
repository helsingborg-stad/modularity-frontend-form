<?php

namespace ModularityFrontendForm\Hydratable;

interface Hydratable
{
    public function hydrate(string $template, array $data): string;
}