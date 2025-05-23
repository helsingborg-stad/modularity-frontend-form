<?php 

namespace ModularityFrontendForm\Api;

use LDAP\Result;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use WpService\WpService;
use ModularityFrontendForm\Api\RestApiParamEnums;
use ModularityFrontendForm\Config\ModuleConfigFactory;
use ModularityFrontendForm\Config\ConfigInterface;
use WP_Error;
use WP_REST_Request;

class RestApiParams implements RestApiParamsInterface
{
  use GetModuleConfigInstanceTrait;

  public function __construct(
    private WpService $wpService,
    private ConfigInterface $config,
    private ModuleConfigFactory $moduleConfigFactory,
  ) {}

  /**
   * Returns the values from the request for the given parameters.
   * 
   * @param WP_REST_Request $request The request to get the values from.
   * @param RestApiParams ...$enum The parameters to get the values for.
   * 
   * @return array The values for the given parameters.
   */
  public function getValuesFromRequest(
    WP_REST_Request $request
  ): object {
    $params = $request->get_params();
    $values = [];
    foreach ($params as $key => $value) {
      $enum = RestApiParamEnums::tryFrom($key);
      if ($enum !== null) {
        $values[$enum->name] = $value ?? null;
      }
    }
    return (object) $values;
  }

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
          RestApiParamEnums::Nonce    => self::getNonceSpecification(),
        };
      }

      return $specifications;
  }

  /**
   * Specification for the post id parameter.
   * 
   * @return WP_Error|true
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
   * @return WP_Error|true
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
   * @return WP_Error|true
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
          $postId = $request->get_params()[
            RestApiParamEnums::PostId->value
          ] ?? null;

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

  /**
   * Specification for the nonce parameter.
   * 
   * @return WP_Error|true
   */
  public function getNonceSpecification(): array
  {
    return [
      'description' => __('The nonce that is used to authenticate the request.', 'modularity-frontend-form'),
      'type'        => 'string',
      'format'      => 'uri',
      'required'    => true,
      'sanitize_callback' => function ($nonce) {
          return $this->wpService->sanitizeTextField($nonce);
      },
      'validate_callback' => function ($nonce, $request) {
          $moduleId = $request->get_params()[
            RestApiParamEnums::ModuleId->value
          ] ?? null;

          $result   =  $this->getModuleConfigInstance(
            $moduleId
          )->getNonceKey();

          if($result === false) {
            return new WP_Error(
              'invalid_nonce',
              __('The nonce provided, does not match the asset.', 'modularity-frontend-form'),
              ['status' => 403]
            );
          }
          return true;
      }
    ];
  }
}
