<?php

namespace ModularityFrontendForm\Handlers;

use AcfService\AcfService;
use ModularityFrontendForm\Validators\Result\ValidationResult;
use ModularityFrontendForm\Validators\Result\ValidationResultInterface;
use WP_Error;
use WpService\WpService;

class FieldsExistsOnPostType implements ValidatorInterface
{
    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private string $postType,
        private array $fields
    ) {
    }

    /**
     * Checks if the request includes fields that are not present on the target post type
     *
     * @param array $fieldMeta The field meta data
     * @param string $postType The post type to check against
     *
     * @return array The invalid field keys
     */
    public function validate(): ?ValidationResultInterface
    {
      //All submitted keys
      $fieldKeys = array_keys($this->fields);

      // Check for field keys that are present on the post type
      $validKeys = $this->filterUnmappedFieldKeysForPostType(
          $fieldKeys,
          $this->postType
      );

      // If there are any stray keys, set an error
      if($strayKeys = array_diff($fieldKeys, $validKeys)) {
        $validationResult = (new ValidationResult())->setError(
          new WP_Error(
            "validation_error", 
            "There was an error", 
            [
              'keys' => $strayKeys
            ]
          )
        );
      }

      return $validationResult ?? null;
    }

    /**
     * Removes fields that are not registered in any of the field groups mapped to the post type
     *
     * @param array $fields The fields to check
     * @param string $postType The post type to check against
     * @param array $defaultKeys The default keys to include, if any.
     * 
     * @return array The filtered fields
     */
    private function filterUnmappedFieldKeysForPostType(array $fieldKeys, string $postType, array $defaultKeys = []): array
    {
        $validKeys = $defaultKeys;

        $fieldGroups = $this->acfService->getFieldGroups(['post_type' => $postType]);

        foreach ($fieldGroups as $group) {
            if(!isset($group['key'])) {
                continue;
            }

            $fields = $this->acfService->acfGetFields($group['key']);

            if(!is_array($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                if (isset($field['key']) && in_array($field['key'], $fieldKeys, true)) {
                    $validKeys[] = $field['key'];
                }
            }
        }

        return array_unique($validKeys);
    }
}