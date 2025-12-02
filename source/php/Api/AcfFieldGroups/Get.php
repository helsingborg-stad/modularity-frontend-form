<?php

namespace ModularityFrontendForm\Api\AcfFieldGroups;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use ModularityFrontendForm\Api\RestApiEndpoint;
use AcfService\AcfService;
use WpService\WpService;

class Get extends RestApiEndpoint
{
    public const NAMESPACE = 'modularity-frontend-form/v1';
    public const ROUTE     = 'acf-field-groups/get';
    public const KEY       = 'getAcfFieldGroups';

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService
    ) {}

    /**
     * Registers a REST route
     *
     * @return bool Whether the route was registered successfully
     */
    public function handleRegisterRestRoute(): bool
    {
        return $this->wpService->registerRestRoute(self::NAMESPACE, self::ROUTE, array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => array($this, 'handleRequest'),
            'permission_callback' => '__return_true',
            'args' => array(
                'post_type' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'The post type to filter field groups by.'
                )
            )
        ));
    }

    /**
     * Handles a REST request to get ACF field groups for a post type
     *
     * @param WP_REST_Request $request The REST request object
     *
     * @return WP_REST_Response The response object
     */
    public function handleRequest(WP_REST_Request $request): WP_REST_Response
    {
        $postType = $request->get_param('post_type');
        if (!$postType) {
            return new WP_REST_Response([
                'error' => 'Missing post_type parameter.'
            ], 400);
        }

        return new WP_REST_Response($this->getStructuredList($this->getAcfGroupsForPostType($postType)), 200);
    }

    private function getStructuredList(array $acfFieldGroups): array
    {
        $structuredList = [];

        foreach ($acfFieldGroups as $group) {
            $key = $group['key'] ?? null;
            $title = $group['title'] ?? null;
            if ($key) {
                $structuredList[$key] = $title;
            }
        }

        return $structuredList;
    }

    /**
     * Get ACF field groups for a specific post type.
     * 
     * @param string $postType The post type to filter field groups by.
     * @return array The filtered ACF field groups.
     */
    private function getAcfGroupsForPostType(string $postType): array
    {

        $groups = $this->acfService->getFieldGroups();

        $filtered = array_filter($groups, function ($group) use ($postType) {
            if (!isset($group['location']) || !is_array($group['location'])) {
                return false;
            }

            foreach ($group['location'] as $locationGroup) {
                if (!is_array($locationGroup)) {
                    continue;
                }

                foreach ($locationGroup as $locationRule) {
                    if (
                        isset($locationRule['param'], $locationRule['value']) &&
                        $locationRule['param'] === 'post_type' &&
                        $locationRule['operator'] === '==' &&
                        $locationRule['value'] === $postType
                    ) {
                        return true;
                    }
                }
            }

            return false;
        });

        return $filtered;
    }
}
