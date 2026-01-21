<?php 

namespace ModularityFrontendForm\Config;

use AcfService\AcfService;
use WpService\WpService;

interface ModuleConfigInterface
{
    public function __construct(
        WpService $wpService,
        AcfService $acfService,
        ConfigInterface $config,
        int $moduleId,
    );

    /**
     * Gets the module ID
     *
     * @return int The module ID
     */
    public function getModuleId(): int;

    /**
     * Gets the module slug
     *
     * @return string The module slug
     */
    public function getModuleSlug(): string;

    /**
     * Gets the module title
     *
     * @return string The module title
     */
    public function getModuleTitle(): string;

    /**
     * Checks if the module is submittable by the current user
     *
     * @return bool Whether the module is submittable by the current user
     */
    public function getModuleIsSubmittableByCurrentUser(): bool;

    /**
     * Checks if the module is editable
     *
     * @return bool Whether the module is editable
     */
    public function getModuleIsEditable(): bool;

    /**
     * Gets the nonce key for the module
     *
     * @return string The nonce key
     */
    public function getNonceKey(): string;

    /**
     * Gets the field keys registered as form fields
     *
     * @param string $property The property to return (key or name)
     * @param bool $includeConditionalFields Whether to include conditional fields
     * @param bool $onlyIncludeRequiredFields Whether to only include required fields
     *
     * @return array|null The field keys or null if none are found
     */
    public function getFieldKeysRegisteredAsFormFields(string $property = 'key', bool $includeConditionalFields = true, bool $onlyIncludeRequiredFields = false): ?array;

    /**
     * Gets the activated handlers for the module
     *
     * @return array The activated handlers
     */
    public function getActivatedHandlers(): array;

    /**
     * Gets the WP DB handler config
     *
     * @return object|null The WP DB handler config or null if none is found
     */
    public function getWpDbHandlerConfig(): ?object;

    /**
     * Gets the mail handler config
     *
     * @return object|null The mail handler config or null if none is found
     */
    public function getMailHandlerConfig(): ?object;

    /**
     * Gets the web hook handler config
     *
     * @return object|null The web hook handler config or null if none is found
     */
    public function getWebHookHandlerConfig(): ?object;

    /**
     * Gets the dynamic post features
     *
     * @return array|null The dynamic post features or null if none are found
     */
    public function getDynamicPostFeatures(): ?array;
}