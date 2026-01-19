<?php
namespace ModularityFrontendForm\Config;

use WpService\WpService;
use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;


enum PostStatus: string
{
  case Publish = 'publish';
  case Private = 'private';
}

class ModuleConfig implements ModuleConfigInterface
{
  public function __construct(
    private WpService $wpService,
    private AcfService $acfService,
    private ConfigInterface $config,
    private int $moduleId
  ) {
    if(!$this->wpService->getPost($this->moduleId)) {
      throw new \Exception('Module not found');
    }
    if($this->wpService->getPostType($this->moduleId) !== $this->config->getModuleSlug()) {
      throw new \Exception('Module is not of type ' . $this->config->getModuleSlug());
    }
  }

  /**
   * @inheritdoc
   */
  public function getModuleId(): int
  {
    return $this->moduleId;
  }

  /**
   * @inheritdoc
   */
  public function getModuleIsSubmittableByCurrentUser(): bool
  {
    $moduleStatus = $this->wpService->getPostStatus($this->getModuleId());
    if($this->wpService->isUserLoggedIn()) {
      if($this->wpService->currentUserCan('edit_post', $this->getModuleId())) {
        return true;
      }
      return in_array($moduleStatus, array_reduce(
        PostStatus::cases(),
        function($carry, PostStatus $status) {
          $carry[] = $status->value;
          return $carry;
        },
        []
      ));
    }
    return in_array($moduleStatus, [PostStatus::Publish->value]);
  }

  public function getModuleIsEditable(): bool
  {
    return true;
  }

  /**
   * @inheritdoc
   */
  public function getModuleSlug(): string
  {
    return $this->wpService->getPostType($this->getModuleId());
  }

  /**
   * @inheritdoc
   */
  public function getModuleTitle(): string
  {
    return $this->wpService->getPost($this->getModuleId())->post_title ?? '';
  }

  /**
   * @inheritdoc
   */
  public function getActivatedHandlers(): array
  {
    return $this->acfService->getField('activeHandlers', $this->getModuleId());
  }

  /**
   * @inheritdoc
   */
  public function getDynamicPostFeatures(): array
  {
    //If key existance, correspond to capability
    $dynamicCapabilityMapper = [
      'post_title'    => 'title',
      'post_content'  => 'editor'
    ];
    $dynamicCapabilities = [];

    $steps = $this->acfService->getField('formSteps', $this->getModuleId());

    if (is_countable($steps) === false || count($steps) === 0) {
      return [];
    }

    foreach($steps as $step) {
      foreach ($dynamicCapabilityMapper as $field => $capability) {
        if (in_array($field, $step['formStepGroup'] ?? [])) {
          $dynamicCapabilities[] = $capability;
        }
      }
    }
    return array_values(array_unique($dynamicCapabilities));
  }

  /**
   * @inheritdoc
   */
  public function getWpDbHandlerConfig(): ?object
  {
    if(in_array('WpDbHandler', $this->getActivatedHandlers()) === false) {
      return null;
    }
    $groupData = $this->acfService->getField('WpDbHandlerConfig', $this->getModuleId());
    if($groupData === null || !is_array($groupData)) {
      return null;
    }
    $groupData['saveToPostType'] = $this->acfService->getField(
      'saveToPostType',
      $this->getModuleId()
    );
    return (object) $groupData;
  }

  /**
   * @inheritdoc
   */
  public function getMailHandlerConfig(): ?object
  {
    if(in_array('MailHandler', $this->getActivatedHandlers()) === false) {
      return null;
    }
    return (object) $this->acfService->getField('MailHandlerConfig', $this->getModuleId());
  }

  /**
   * @inheritdoc
   */
  public function getWebHookHandlerConfig(): ?object
  {
    if(in_array('WebHookHandler', $this->getActivatedHandlers()) === false) {
      return null;
    }
    return (object) $this->acfService->getField('WebHookHandlerConfig', $this->getModuleId());
  }

  /**
   * @inheritdoc
   */
  public function getFieldKeysRegisteredAsFormFields(string $property = 'key', bool $includeConditionalFields = true): ?array
  {
    $steps = $this->acfService->getField('formSteps', $this->getModuleId());
    if ($steps === null) {
        return null;
    }
    $fieldKeys = [];
    foreach ($steps as $step) {
        if (!isset($step['formStepGroup']) || !is_array($step['formStepGroup']) || count($step['formStepGroup']) === 0) {
            continue;
        }

        foreach($step['formStepGroup'] as $group) {
          if(!isset($fields)) {
            $fields = [];
          }
          $fields = array_merge($fields, acf_get_fields($group));
        }

        if (!$includeConditionalFields) {
            $fields = array_filter($fields, function ($field) {
                $hasConditionalLogic   = (empty($field['conditional_logic']) || $field['conditional_logic'] === 0) ? false : true;
                $isNotRequiredRepeater = (!$field['required'] && $field['type'] !== 'repeater');
                return $hasConditionalLogic && $isNotRequiredRepeater;
            });
        }

        if (!is_array($fields)) {
            continue;
        }

        foreach ($fields as $field) {
            $fieldKeys = array_merge($fieldKeys, $this->getFieldKeysRecursive($field, $property));
        }
    }

    return array_values(array_unique($fieldKeys));
  }

  /**
   * Recursively gets field keys from a field array
   *
   * @param array $field The field array
   *
   * @return array The field keys
   */
  private function getFieldKeysRecursive(array $field, string $property = 'key'): array
  {
    $items = [];

    if (isset($field[$property]) && str_starts_with($field['key'], 'field_')) {
        $items[] = $field[$property];
    }

    if (isset($field['sub_fields']) && is_array($field['sub_fields'])) {
        foreach ($field['sub_fields'] as $subField) {
            $items = array_merge($items, $this->getFieldKeysRecursive($subField, $property));
        }
    }

    return $items;
  }

  /**
   * @inheritdoc
   */
  public function getNonceKey(): string
  {
    $moduleData = $this->wpService->getPost($this->getModuleId());
    $moduleData = serialize($moduleData);
    return md5($moduleData);
  }
}