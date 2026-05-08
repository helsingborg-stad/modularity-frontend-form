<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\WpService;

class GroupHelper
{
    private $wordpressStandardFieldsKey = 'wp-standard-fields';

    public function __construct(
        private AcfService $acfService,
        private WpService $wpService
    ) {}

    /**
     * Get all groups
     * 
     * @return array Structured list of ACF groups grouped by post type
     */
    public function getGroups(): array
    {
        static $groups = null;

        if ($groups !== null) {
            return $groups;
        }

        $groups = $this->getFieldGroups();
        $groups = $this->buildPostTypeGroupedList($groups);
        $groups[$this->wordpressStandardFieldsKey] = $this->getBasicWordpressFields();

        return $groups;
    }

    /**
     * Get a flat list of all ACF groups
     * 
     * @return array Flat list of ACF groups with key as group key and value as group title
     */
    public function getFlatGroups(): array
    {
        static $flatList = null;

        if ($flatList !== null) {
            return $flatList;
        }

        $groups = $this->getFieldGroups();

        $flatList = [];
        foreach ($groups as $group) {
            if (isset($group['key'], $group['title'])) {
                $flatList[$group['key']] = $group['title'];
            }
        }

        foreach ($this->getBasicWordpressFields() as $key => $value) {
            $flatList[$key] = $value;
        }

        return $flatList;
    }

    public function getBasicWordpressFields(): array
    {
        return [
            'post_title'    => $this->wpService->__('Post title', 'modularity-frontend-form'),
            'post_content'  => $this->wpService->__('Post content', 'modularity-frontend-form')
        ];
    }
    /**
     * Build a structured list of groups grouped by post type
     * 
     * @param array $groups List of groups
     * @return array Structured list of groups grouped by post type
     */
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

    private function getFieldGroups(): array 
    {
        static $groups = null;

        if ($groups === null) {
            $groups = $this->acfService->getFieldGroups();
        }

        return $groups;
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
