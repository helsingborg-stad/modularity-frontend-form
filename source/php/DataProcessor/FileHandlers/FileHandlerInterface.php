<?php 

namespace ModularityFrontendForm\DataProcessor\FileHandlers;

use ModularityFrontendForm\Config\Config;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use WP_REST_Request;
use WpService\WpService;
use WP_Error;
interface FileHandlerInterface {
    public function __construct(
      Config $config, 
      ModuleConfigInterface $moduleConfig,
      WpService $wpService
    );

    /**
     * Handle file uploads from the request and attach them to the specified post.
     *
     * @param WP_REST_Request $request The REST request containing file uploads.
     * @param int|null $postId The ID of the post to attach files to, if any.
     * @return WP_Error|array An array of uploaded file info or a WP_Error on failure.
     */
    public function handle(WP_REST_Request $request, ?int $postId = null) : WP_Error|array;
}