<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\WpService;

class AcfGroupHelper
{
    private $wordpressStandardFieldsKey = 'wp-standard-fields';

    public function __construct(
        private AcfService $acfService,
        private WpService $wpService
    ) {}

    /**
     * Get all ACF groups structured by post type
     * 
     * @return array Structured list of ACF groups grouped by post type
     */
    public function getAcfGroups(): array
    {
        static $groups = null;

        if ($groups !== null) {
            return $groups;
        }

        $groups = $this->getAcfFieldGroups();
        $groups = $this->buildPostTypeGroupedList($groups);
        $groups = $this->addBasicWordpressFields($groups);

        return $groups;
    }

    /**
     * Get a flat list of all ACF groups
     * 
     * @return array Flat list of ACF groups with key as group key and value as group title
     */
    public function getFlatAcfGroups(): array
    {
        static $flatList = null;

        if ($flatList !== null) {
            return $flatList;
        }

        $groups = $this->getAcfFieldGroups();

        $flatList = [];
        foreach ($groups as $group) {
            if (isset($group['key'], $group['title'])) {
                $flatList[$group['key']] = $group['title'];
            }
        }

        foreach ($this->addBasicWordpressFields([])[$this->wordpressStandardFieldsKey] as $key => $value) {
            $flatList[$key] = $value;
        }

        return $flatList;
    }

    private function addBasicWordpressFields(array $data): array
    {
        $data[$this->wordpressStandardFieldsKey] = [
            'post_title'    => $this->wpService->__('Post title', 'modularity-frontend-form'),
            'post_content'  => $this->wpService->__('Post content', 'modularity-frontend-form')
        ];

        return $data;
    }
    /**
     * Build a structured list of ACF groups grouped by post type
     * 
     * @param array $groups List of ACF groups
     * @return array Structured list of ACF groups grouped by post type
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

    private function getAcfFieldGroups(): array 
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
