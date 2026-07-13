<?php

namespace ModularityFrontendForm\FieldVisibility;

use ModularityFrontendForm\Hookable\Hookable;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetPostType;
use WpService\Contracts\IsAdmin;

class AllowFieldsToBeHiddenFromFrontendOrBackendForms implements Hookable {

    public function __construct(private AddFilter&IsAdmin&GetPostType $wpService)
    {   
    }

    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/load_fields', array($this, 'filterByVisibility'), 10, 1);
    }

    public function filterByVisibility(array $fields): array
    {
        $fields = array_filter($fields, function($field) {
            return !$this->fieldShouldBeHiddenFromFrontend($field);
        });
        $fields = array_filter($fields, function($field) {
            return !$this->fieldShouldBeHiddenFromBackend($field);
        });

        return array_values($fields);
    }

    private function fieldShouldBeHiddenFromFrontend(array $field):bool {
        if($this->wpService->isAdmin()) {
            return false;
        }
        if( !isset($field['is_publicly_hidden'])) {
            return false;
        }
        return (bool)$field['is_publicly_hidden'] === true;
    }

    private function fieldShouldBeHiddenFromBackend(array $field):bool {
        if( !$this->wpService->isAdmin() ) {
            return false;
        }
        if( $this->userIsOnAcfFieldGroupScreen() || $this->savingAcfFieldGroup() ) {
            return false;
        }
        if( !isset($field['is_privately_hidden'])) {
            return false;
        }

        return (bool)$field['is_privately_hidden'] === true;
    }

    private function userIsOnAcfFieldGroupScreen(): bool {
    
        global $pagenow;

        return $this->wpService->isAdmin()
            && $pagenow === 'post.php'
            && isset($_GET['post'])
            && $this->wpService->getPostType((int) $_GET['post']) === 'acf-field-group';
    }

    private function savingAcfFieldGroup(): bool {
        global $pagenow;

        return $this->wpService->isAdmin()
            && $pagenow === 'post.php'
            && isset($_POST['post_ID'])
            && $this->wpService->getPostType((int) $_POST['post_ID']) === 'acf-field-group';
    }
}