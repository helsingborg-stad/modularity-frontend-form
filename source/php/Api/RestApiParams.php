<?php 

namespace ModularityFrontendForm\Api;

use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use WpService\WpService;
use ModularityFrontendForm\Api\RestApiParamEnums;
use ModularityFrontendForm\Config\ModuleConfigFactory;

class RestApiParams 
{
  use GetModuleConfigInstanceTrait;

  public function __construct(
    private WpService $wpService,
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
      'validate_callback' => function ($postId) {
          return $this->wpService->getPost($postId) !== null;
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
      'validate_callback' => function ($moduleId) {
          return $this->getModuleConfigInstance(
              $moduleId
          )->getModuleIsSubmittable();
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
      'validate_callback' => function ($token, $request) {
          $postId = $request->get_params()['post-id'] ?? null;
          $post = $this->wpService->getPost($postId);
          if ($post === null) {
              return false;
          }
          return (bool) $token === $post->post_password;
      }
    ];
  }
}
