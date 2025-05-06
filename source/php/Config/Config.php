<?php 

namespace ModularityFrontendForm\Config;

use WpService\Contracts\ApplyFilters;

class Config implements ConfigInterface
{
  public function __construct(
    private ApplyFilters $wpService,
    private string $filterPrefix
  ){}

  /**
   * The module post type / slug.
   * 
   * Note: mod- prefix is added according 
   * to modularity standards.
   * 
   * @return string
   */
  public function getModuleSlug(): string
  {
    return "mod-" . $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'frontend-form'
    );
  }

  /**
   * Get the current nonce key.
   * 
   * @return string
   */
  public function getNonceKey(): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'modulariyFontendFormNonce'
    );
  }

  /**
   * Get the filter prefix.
   * 
   * @return string
   */
  public function getFilterPrefix(): string
  {
    return rtrim($this->filterPrefix, "/") . "/";
  }

  /**
   * Create a prefix for image conversion filter.
   *
   * @return string
   */
  public function createFilterKey(string $filter = ""): string
  {
    return $this->getFilterPrefix() . ucfirst($filter);
  }
}