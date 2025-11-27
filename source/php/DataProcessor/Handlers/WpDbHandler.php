<?php

namespace ModularityFrontendForm\DataProcessor\Handlers;

use WpService\WpService; 
use AcfService\AcfService; 
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResult;
use ModularityFrontendForm\DataProcessor\Handlers\Result\HandlerResultInterface;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;
use WP_Error;

class WpDbHandler implements HandlerInterface {

  use GetModuleConfigInstanceTrait;

  public function __construct(
      private WpService $wpService,
      private AcfService $acfService,
      private ConfigInterface $config,
      private ModuleConfigInterface $moduleConfigInstance,
      private HandlerResultInterface $handlerResult = new HandlerResult()
  ) {
  }

  /**
   * Handles the request to insert a post in WordPress 
   * database. 
   * 
   * @param array $data The data to insert
   * 
   * @return HandlerResultInterface|null The result of the handler
   */
  public function handle(array $data): ?HandlerResultInterface
  {
    if(in_array('post_id', $data) && get_post($data['post_id']) !== null) {
      $this->updatePost(
        $this->moduleConfigInstance->getModuleId(),
        $data
      );
    } else {
      $this->insertPost(
        $this->moduleConfigInstance->getModuleId(),
        $data
      );
    }
    return $this->handlerResult;
  }

  /**
   * Handles the request to insert a post
   *
   * @param int|null $moduleID The module ID
   * @param array|null $fieldMeta The field meta data
   *
   * @return WP_Error|int The result of the post insertion
   */
  private function insertPost(int $moduleID, array|null $fieldMeta): false|int {

    $moduleConfig = $this->moduleConfigInstance->getWpDbHandlerConfig();

    $result = $this->wpService->wpInsertPost([
        'post_title'    => $this->moduleConfigInstance->getModuleTitle(),
        'post_type'     => $moduleConfig->saveToPostType,
        'post_status'   => $moduleConfig->saveToPostTypeStatus,
        'post_password' => $this->createPostPassword(),
        'meta_input'    => [
          $this->config->getMetaDataNamespace('module_id')  => $moduleID,
          $this->config->getMetaDataNamespace('nonce')      => $fieldMeta['nonce'] ?? '',
          $this->config->getMetaDataNamespace('submission') => true
        ],
    ]);

    // Set error 
    if ($this->wpService->isWpError($result)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value,
          $this->wpService->__('Could not insert post.', 'modularity-frontend-form'),
          [
            'post_type' => $moduleConfig->saveToPostType,
            'post_status' => $moduleConfig->saveToPostTypeStatus,
            'post_id'   => $result->get_error_data(),
          ]
        )
      );
      return false;
    }
    $this->storeFields($fieldMeta, $result);
    return true;
  }

  private function updatePost(int $moduleID, array|null $fieldMeta): false|int {

    $moduleConfig = $this->moduleConfigInstance->getWpDbHandlerConfig();

    $result = $this->wpService->wpUpdatePost([
        'ID'           => $fieldMeta['post_id'],
        'post_title'   => $this->moduleConfigInstance->getModuleTitle(),
        'post_status'  => $moduleConfig->saveToPostTypeStatus,
        'meta_input'   => [
          $this->config->getMetaDataNamespace('module_id') => $moduleID,
          $this->config->getMetaDataNamespace('nonce')     => $fieldMeta['nonce'] ?? '',
        ],
    ]);

    // Set error 
    if ($this->wpService->isWpError($result)) {
      $this->handlerResult->setError(
        new WP_Error(
          RestApiResponseStatusEnums::HandlerError->value,
          $this->wpService->__('Could not update post.', 'modularity-frontend-form'),
          [
            'post_type' => $moduleConfig->saveToPostType,
            'post_status' => $moduleConfig->saveToPostTypeStatus,
            'post_id'   => $result->get_error_data(),
          ]
        )
      );
      return false;
    }
    $this->storeFields($fieldMeta, $result);
    return true;
  }

  /**
   * Stores the fields in the database
   *
   * @param array $fields The fields to store
   * @param int $postId The ID of the post to store the fields for
   */
  public function storeFields(array $fields, int $postId): bool
  {
    foreach ($fields as $key => $value) {
      if (isset($fields[$key])) {
        $result = $this->acfService->updateField(
            $key, 
            $value, 
            $postId
        );
        if($this->wpService->isWpError($result)) {
          $this->handlerResult->setError(
            new WP_Error(
              RestApiResponseStatusEnums::HandlerError->value,
              $this->wpService->__('Could not update field with metadata.', 'modularity-frontend-form'),
              [
                'field' => $key,
                'post_id' => $postId,
              ]
            )
          );
          return false;
        }
      }
    }
    return true;
  }

  /**
   * Creates a post password
   *
   * @return string The generated password
   */
  private function createPostPassword(): string
  {
    return $this->wpService->wpGeneratePassword(
      32,
      false,
      false
    );
  }
}
