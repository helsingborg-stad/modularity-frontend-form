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

class IsEditableValidator implements ValidatorInterface
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
        // Check if the module is editable
        if(!$this->moduleHasEditingEnabled()) {
            return $this->validationResult;
        }

        // Check if the target post has a registered token
        $postId = $data['postId'] ?? null;
        if(!$this->postHasARegisteredToken($postId)) {
            return $this->validationResult;
        }

        return $this->validationResult;
    }

    /**
     * Check if the module is editable
     *
     * @return bool
     */
    private function moduleHasEditingEnabled() {
        
        $moduleId = $this->moduleConfigInstance->getModuleId();
        if($this->acfService->getField('editingEnabled', $moduleId)) {
            return true;
        }

        $this->validationResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value, 
                $this->wpService->__(
                    'This form has the editing feature disabled.', 'modularity-frontend-form'
                )
            )
        );

        return false;
    }

    /**
     * Check if the post has a registered token
     *
     * @return bool
     */
    private function postHasARegisteredToken(int $postId): bool {
        
        if($this->acfService->getField('token', $postId)) {
            return true;
        }

        $this->validationResult->setError(
            new WP_Error(
                RestApiResponseStatusEnums::ValidationError->value, 
                $this->wpService->__(
                    'The post does not have a registered security token.', 'modularity-frontend-form'
                )
            )
        );

        return false;
    }
}