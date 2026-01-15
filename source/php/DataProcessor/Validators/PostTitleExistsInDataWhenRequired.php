<?php

namespace ModularityFrontendForm\DataProcessor\Validators;

use AcfService\AcfService;
use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Config\ModuleConfigInterface;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResult;
use ModularityFrontendForm\DataProcessor\Validators\Result\ValidationResultInterface;
use WP_Error;
use WpService\WpService;
use ModularityFrontendForm\Config\GetModuleConfigInstanceTrait;
use ModularityFrontendForm\Api\RestApiResponseStatusEnums;

class PostTitleExistsInDataWhenRequired implements ValidatorInterface
{
    use GetModuleConfigInstanceTrait;

    public function __construct(
        private WpService $wpService,
        private AcfService $acfService,
        private ConfigInterface $config,
        private ModuleConfigInterface $moduleConfigInstance,
        private ValidationResultInterface $validationResult = new ValidationResult()
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate($data): ?ValidationResultInterface
    {
        // Check if the form has a native post title configured
        if(!$this->moduleHasNativePostTitle()) {
            return $this->validationResult;
        }

        // Check if the submitted data contains a post title
        if(!$this->dataIncludesPostTitle($data)) {
            return $this->validationResult;
        }

        return $this->validationResult;
    }

    /**
     * Check if the module has a native post title
     *
     * @return bool
     */
    private function moduleHasNativePostTitle() {
        $dynamicPostFeatures = $this->moduleConfigInstance->getDynamicPostFeatures();
        if(in_array('title', $dynamicPostFeatures)) {
            return true;
        }
        return false;
    }

    /**
     * Check if submitted data includes a post title
     *
     * @return bool
     */
    private function dataIncludesPostTitle(array $data): bool {
        $postDataPrefix = $this->config->getFieldNamespace();

        if(isset($data[$postDataPrefix]['post_title']) && !empty(trim($data[$postDataPrefix]['post_title']))) {
            return true;
        }

        $this->validationResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value, 
                $this->wpService->__(
                    'A title is required in this form. The field cannot be empty.', 'modularity-frontend-form'
                )
            )
        );

        return false;
    }
}