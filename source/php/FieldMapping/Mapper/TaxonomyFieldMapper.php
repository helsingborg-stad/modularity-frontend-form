<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper;
use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;

class TaxonomyFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    public function map(): ?array
    {
        $this->field['choices'] = $this->structureTerms(
            $this->getTermsFromTaxonomy($this->field)
        );

        $this->field['field_type'] = $this->field['field_type'] ?? 'checkbox';

        return (new Mapper($this->field, $this->wpService))->map();
    }

    /**
     * Structure terms into a key-value array.
     *
     * @param array $terms The terms to structure.
     * @return array The structured terms.
     */
    private function structureTerms(array $terms): array
    {
        $structured = [];

        foreach ($terms as $term) {
            $structured[$term->term_id] = $term->name ?? $term->term_id;
        }

        return $structured;
    }

    /**
     * Get terms from the specified taxonomy.
     *
     * @param array $field The field configuration.
     * @return array The terms from the taxonomy.
     */
    private function getTermsFromTaxonomy(array $field): array
    {
        if (empty($field['taxonomy'])) {
            return [];
        }

        $terms = $this->wpService->getTerms([
            'taxonomy'   => $field['taxonomy'],
            'hide_empty' => false,
        ]);

        return $this->wpService->isWpError($terms) ? [] : $terms;
    }
}