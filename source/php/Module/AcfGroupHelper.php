<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\WpService;

class AcfGroupHelper
{
    public function __construct(
        private WpService $wpService,
        private AcfService $acfService
    ) {}

    public function getAcfGroups(): array
    {
        $groups = $this->acfService->getFieldGroups();
        return $this->buildPostTypeGroupedList($groups);
    }

    private function buildPostTypeGroupedList(array $groups): array
    {
        $structured = [];

        foreach ($groups as $group) {
            if (!isset($group['key'], $group['title'], $group['location'])) {
                continue;
            }

            $postTypes = $this->extractPostTypesFromLocations($group['location']);

            foreach ($postTypes as $postType) {
                $structured[$postType][$group['key']] = $group['title'];
            }
        }

        return $structured;
    }

    /**
     * Extract all post types from ACF group location rules
     */
    private function extractPostTypesFromLocations(array $locations): array
    {
        $postTypes = [];

        foreach ($locations as $locationGroup) {
            if (!is_array($locationGroup)) {
                continue;
            }

            foreach ($locationGroup as $rule) {
                if (
                    isset($rule['param'], $rule['operator'], $rule['value']) &&
                    $rule['param'] === 'post_type' &&
                    $rule['operator'] === '=='
                ) {
                    $postTypes[] = $rule['value'];
                }
            }
        }

        return array_unique($postTypes);
    }
}
