<?php 

namespace ModularityFrontendForm\Api;

use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use WpService\WpService;
use ModularityFrontendForm\Api\RestApiParamEnums;
use ModularityFrontendForm\Config\ModuleConfigFactory;
use ModularityFrontendForm\Config\ConfigInterface;
use WP_Error;

class RestApiParams 
{
  use GetModuleConfigInstanceTrait;

  public function __construct(
    private WpService $wpService,
    private ConfigInterface $config,
    private ModuleConfigFactory $moduleConfigFactory,
  ) {}

  /**
   * Returns the specification for the given parameter.
   * 
   * @param RestApiParams ...$enum The parameter to get the specification for.
   * 
   * @return array The specification for the given parameter.
   */
  public function getParamSpecification(RestApiParamEnums ...$enums): array
  {
      $specifications = [];

      foreach ($enums as $enum) {
        $specifications[$enum->value] = match ($enum) {
          RestApiParamEnums::PostId   => self::getPostIdSpecification(),
          RestApiParamEnums::ModuleId => self::getModuleIdSpecification(),
          RestApiParamEnums::Token    => self::getTokenSpecification(),
        };
      }

      return $specifications;
  }

  /**
   * Specification for the post id parameter.
   * 
   * @return array
   */
  private function getPostIdSpecification(): array
  {
    return [
      'description' => __('The post id that stores the form data.', 'modularity-frontend-form'),
      'type'        => 'integer',
      'format'      => 'uri',
      'required'    => true,
      'sanitize_callback' => function ($moduleId) {
          return intval($moduleId);
      },
      'validate_callback' => function ($postId) {
          $result =  $this->wpService->getPost($postId) !== null;
          if($result === false) {
            return new WP_Error(
              'invalid_post_id',
              __('The resource you are trying to find, does not exist.', 'modularity-frontend-form'),
              ['status' => 404]
            );
          }
          return true;
      }
    ];
  }


  /**
   * Specification for the module id parameter.
   * 
   * @return array
   */
  private function getModuleIdSpecification(): array
  {
    return [
      'description' => __('The module id that the request originates from', 'modularity-frontend-form'),
      'type'        => 'integer',
      'format'      => 'uri',
      'required'    => true,
      'sanitize_callback' => function ($moduleId) {
          return intval($moduleId);
      },
      'validate_callback' => function ($moduleId) {
          $result =  $this->getModuleConfigInstance(
              $moduleId
          )->getModuleIsSubmittableByCurrentUser();

          if($result === false) {
            return new WP_Error(
              'invalid_module_id',
              __('The forms created by this module cannot be edited at this moment.', 'modularity-frontend-form'),
              ['status' => 404]
            );
          }
          return true;
      }
    ];
  }

  /**
   * Specification for the token parameter.
   * 
   * @return array
   */
  private function getTokenSpecification(): array
  {
    return [
      'description' => __('The token that is used to authenticate the request.', 'modularity-frontend-form'),
      'type'        => 'string',
      'format'      => 'uri',
      'required'    => true,
      'sanitize_callback' => function ($token) {
          return substr($this->wpService->sanitizeTextField($token), 0, 32);
      },
      'validate_callback' => function ($token, $request) {
          $postId = $request->get_params()['post-id'] ?? null;
          $post   = $this->wpService->getPost($postId);

          // Not a post
          if(is_a($post, 'WP_Post') === false) {
            return new WP_Error(
              'invalid_post_id',
              __('The resource you are trying to find, does not exist.', 'modularity-frontend-form'),
              ['status' => 404]
            );
          }

          // Unprotected post
          if(empty($post->post_password)) {
            return new WP_Error(
              'asset_not_protected',
              __('The resource you are trying to access is not editable.', 'modularity-frontend-form'),
              ['status' => 403]
            );
          }

          //Not matching the token
          if($token !== $post->post_password) {
            return new WP_Error(
              'invalid_token',
              __('The token provided, does not match the asset.', 'modularity-frontend-form'),
              ['status' => 403]
            );
          }

          return true;
      }
    ];
  }
}
