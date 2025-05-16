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

class TokenValidator implements ValidatorInterface
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
        $token = $data['token'] ?? null;

        if(!$token) {
            $this->validationResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::ValidationError->value, 
                    $this->wpService->__(
                        'Token is missing in request.',
                    ), 
                    [
                        'fields' => [
                            'key' => 'token'
                        ],
                    ]
                )
            );
            return $this->validationResult;
        }

        // Check if the token is valid
        $postId = $data['postId'] ?? null;
        $tokenField = $this->acfService->getField('token', $postId);
        if($tokenField !== $token) {
            $this->validationResult->setError(
                new WP_Error(
                    RestApiResponseStatusEnums::ValidationError->value, 
                    $this->wpService->__(
                        'Token provided does not match the one registered for this post.',
                    )
                )
            );
        }

        return $this->validationResult;
    }
}