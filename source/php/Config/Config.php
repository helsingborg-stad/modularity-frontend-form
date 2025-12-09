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
   * Returns a list of keys that is irellevant to the end user.
   * 
   * @return array
   */
  public function getUnprintableKeys(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      [
        'nonce',
        'postId',
      ]
    );
  }

  /**
   * The POST field namespace (all POST keys will be contained in this namespace).
   * 
   * @return string
   */
  public function getFieldNamespace(null|string $fieldName = null): string
  {
    $namespace = $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'mod-frontend-form'
    );
    return $fieldName ? "{$namespace}[{$fieldName}]" : $namespace;
  }

  /**
   * The meta data namespace (all meta data keys will be prefixed with this "namespace").
   * 
   * @return string
   */
  public function getMetaDataNamespace(?string $key = null): string
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      'mod_frontend_form' . ($key ? '_' . $key : '')
    );
  }

  /**
   * A list of keys that should be bypassed in the validation towards acf functions.
   * These keys are manually secured by other validation methods.
   */
  public function getKeysToBypass(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      [
        //General keys
        'postId',
        'nonce',

        //WordPress native post keys
        'post_title',
        'post_content',
      ]
    );
  }

  /**
   * Get allowed HTML tags for user input.
   * 
   * @return array
   */
  public function getAllowedHtmlTags(): array
  {
    return $this->wpService->applyFilters(
      $this->createFilterKey(__FUNCTION__), 
      [
        'a' => [
          'href' => true,
          'title' => true,
          'target' => true,
          'rel' => true,
        ],
        'br' => [],
        'em' => [],
        'strong' => [],
        'p' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
      ]
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