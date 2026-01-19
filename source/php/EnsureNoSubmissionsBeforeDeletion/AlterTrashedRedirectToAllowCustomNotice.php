<?php

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Hookable\Hookable;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;
use WpService\Contracts\AddQueryArg;
use WpService\Contracts\RemoveQueryArg;

class AlterTrashedRedirectToAllowCustomNotice implements Hookable {
    public function __construct(private ConfigInterface $config, private AddAction&AddFilter&RemoveQueryArg&AddQueryArg $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addAction(EnsureNoSubmissionsBeforeDeletion::createHookName('trash_prevented'),[$this, 'alterTrashedRedirect']);
        $this->wpService->addAction(EnsureNoSubmissionsBeforeDeletion::createHookName('deletion_prevented'),[$this, 'alterDeletedRedirect']);
    }

    public function alterTrashedRedirect(\WP_Post $post): void {
        $this->wpService->addFilter('wp_redirect', fn($location) => $this->modifyRedirectForTrashedPost($location, $post), 10, 1);
    }

    public function alterDeletedRedirect(\WP_Post $post): void {
        $this->wpService->addFilter('wp_redirect', fn($location) => $this->modifyRedirectForDeletedPost($location, $post), 10, 1);
    }

    public function modifyRedirectForTrashedPost(string $location, \WP_Post $post):string {
        
        if(strpos($location, 'trashed=') === false) {
            return $location;
        }

        $location = $this->wpService->removeQueryArg('trashed', $location);
        $location = $this->wpService->addQueryArg($this->getParameterName(), $post->ID, $location);

        return $location;
    }

    public function modifyRedirectForDeletedPost(string $location, \WP_Post $post):string {
        
        if(strpos($location, 'deleted=') === false) {
            return $location;
        }

        $location = $this->wpService->removeQueryArg('deleted', $location);
        $location = $this->wpService->addQueryArg($this->getDeletedParameterName(), $post->ID, $location);

        return $location;
    }

    private function getParameterName(): string {
        return $this->config->getModuleSlug() . '-trash-prevented';
    }

    private function getDeletedParameterName(): string {
        return $this->config->getModuleSlug() . '-deletion-prevented';
    }
}