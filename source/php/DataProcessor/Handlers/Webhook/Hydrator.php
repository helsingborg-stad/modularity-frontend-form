<?php

namespace ModularityFrontendForm\DataProcessor\Handlers\Webhook;

interface Hydrator
{
    public function hydrate(string $template, array $data): string;
}
