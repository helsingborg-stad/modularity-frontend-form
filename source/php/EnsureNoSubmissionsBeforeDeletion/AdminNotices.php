<?php

namespace ModularityFrontendForm\EnsureNoSubmissionsBeforeDeletion;

use ModularityFrontendForm\Config\ConfigInterface;
use ModularityFrontendForm\Hookable\Hookable;
use WpService\Contracts\__;
use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetQueryVar;
use WpService\Contracts\WpAdminNotice;

class AdminNotices implements Hookable {
    public function __construct(private ConfigInterface $config, private AddAction&AddFilter&WpAdminNotice&__&GetQueryVar $wpService)
    {
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('query_vars', [$this, 'registerQueryVar']);
        $this->wpService->addAction('admin_notices',[$this, 'adminNotices']);
    }

    public function registerQueryVar(array $queryVars): array {
        $queryVars[] = $this->getTrashedQueryVar();
        $queryVars[] = $this->getDeletedQueryVar();

        return $queryVars;
    }

    public function adminNotices(): void {        
        if($this->wpService->getQueryVar($this->getTrashedQueryVar())) {
            $this->addTrashPreventedNotice();
        }
        if($this->wpService->getQueryVar($this->getDeletedQueryVar())) {
            $this->addDeletionsPreventedNotice();
        }
    }

    public function addDeletionsPreventedNotice(): void {
        $this->printNotice( $this->wpService->__('Cannot delete form module: existing submissions found.', 'modularity-frontend-form'), );
    }

    public function addTrashPreventedNotice(): void {
        $this->printNotice($this->wpService->__('Cannot move form module to trash: existing submissions found.', 'modularity-frontend-form'),);
    }

    private function printNotice(string $message): void {
        $this->wpService->wpAdminNotice($message, ['type' => 'warning', 'dismissible' => true]);
    }

    private function getTrashedQueryVar(): string {
        return $this->config->getModuleSlug() . '-trash-prevented';
    }

    private function getDeletedQueryVar(): string {
        return $this->config->getModuleSlug() . '-deletion-prevented';
    }
}