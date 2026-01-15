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
use WP_REST_Request;

class PostContentExistsInDataWhenRequired implements ValidatorInterface
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
    public function validate(array $data, WP_REST_Request $request): ?ValidationResultInterface
    {
        // Check if the form has a native post content configured
        if(!$this->moduleHasNativePostContent()) {
            return $this->validationResult;
        }

        // Check if the submitted data contains a post content
        if(!$this->dataIncludesPostContent($data)) {
            return $this->validationResult;
        }

        return $this->validationResult;
    }

    /**
     * Check if the module has a native post content
     *
     * @return bool
     */
    private function moduleHasNativePostContent() {
        $dynamicPostFeatures = $this->moduleConfigInstance->getDynamicPostFeatures();
        if(in_array('content', $dynamicPostFeatures)) {
            return true;
        }
        return false;
    }

    /**
     * Check if submitted data includes a post content
     *
     * @return bool
     */
    private function dataIncludesPostContent(array $data): bool {
        $postDataPrefix = $this->config->getFieldNamespace();

        if(isset($data[$postDataPrefix]['post_content']) && !empty(trim($data[$postDataPrefix]['post_content']))) {
            return true;
        }

        $this->validationResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value, 
                $this->wpService->__(
                    'Content is required in this form. The field cannot be empty.', 'modularity-frontend-form'
                )
            )
        );

        return false;
    }
}