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
    public function validate($data): ?ValidationResultInterface
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
                    "validation_error_nonce", 
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
        $nonceKey = $this->wpService->wpCreateNonce(
            $this->moduleConfigInstance->getNonceKey()
        );
        if ($nonceKey !== $data['nonce']) {
            $this->validationResult->setError(
                new WP_Error(
                    "validation_error_nonce", 
                    $this->wpService->__('Nonce is invalid.', 'modularity-frontend-form')
                )
            );
            return false;
        }
        return true;
    }
}