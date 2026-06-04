<?php

namespace ModularityFrontendForm\Helper;

interface Hydrator
{
    public function hydrate(string $template, array $data): string;
}
