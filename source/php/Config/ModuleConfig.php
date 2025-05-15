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
    if(!$this->wpService->getPostType($this->moduleId) === $this->config->getModuleSlug()) {
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
  public function getModuleIsSubmittable(): bool
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
  public function getWpDbHandlerConfig(): ?object
  {
    if(in_array('WpDbHandler', $this->getActivatedHandlers()) === false) {
      return null;
    }
    return (object) $this->acfService->getField('WpDbHandlerConfig', $this->getModuleId());
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
  public function getNonceKey(): string
  {
    $moduleData = $this->wpService->getPost($this->getModuleId());
    $moduleData = serialize($moduleData);
    return md5($moduleData);
  }
}