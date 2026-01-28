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

class NonceValidator implements ValidatorInterface
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
        if ($this->checkNoncePresence($data) === false) {
            return $this->validationResult;
        }

        if ($this->checkNonceValidity($data) === false) {
            return $this->validationResult;
        }

        return $this->validationResult;
    }

    /**
     * Check if the nonce is present in the data
     *
     * @param array $data The data to check
     * @return bool True if the nonce is present, false otherwise
     */
    private function checkNoncePresence($data): bool
    {
        if (!isset($data['nonce'])) {
            $this->validationResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::ValidationError->value, 
                    $this->wpService->__('Nonce missing.', 'modularity-frontend-form')
                )
            );
            return false;
        }
        return true;
    }

    /**
     * Check if the nonce is valid
     *
     * @param array $data The data to check
     * @return bool True if the nonce is valid, false otherwise
     */
    private function checkNonceValidity($data): bool
    {
        $verified = $this->wpService->wpVerifyNonce(
            $data['nonce'],
            $this->moduleConfigInstance->getNonceKey()
        );
        
        // wp_verify_nonce returns false for invalid nonce, 1 or 2 for valid
        // false (0) = invalid/expired, 1 = valid within last 12 hours, 2 = valid 12-24 hours ago
        if ($verified === false || $verified === 0) {
            $this->validationResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::ValidationError->value, 
                    $this->wpService->__('Nonce is invalid or expired.', 'modularity-frontend-form')
                )
            );
            return false;
        }
        return true;
    }
}