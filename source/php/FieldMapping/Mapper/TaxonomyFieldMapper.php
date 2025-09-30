<?php

namespace ModularityFrontendForm\FieldMapping\Mapper;

use ModularityFrontendForm\FieldMapping\Mapper\Interfaces\FieldMapperInterface;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperConstruct;
use ModularityFrontendForm\FieldMapping\Mapper\Traits\FieldMapperGetInstance;
use ModularityFrontendForm\FieldMapping\Mapper\CheckboxFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\SelectFieldMapper;
use ModularityFrontendForm\FieldMapping\Mapper\RadioFieldMapper;

class TaxonomyFieldMapper implements FieldMapperInterface
{
    use FieldMapperConstruct;
    use FieldMapperGetInstance;

    private array $mapClasses = [
        'checkbox'     => CheckboxFieldMapper::class,
        'radio'        => RadioFieldMapper::class,
        'select'       => SelectFieldMapper::class,
        'multi_select' => SelectFieldMapper::class,
    ];

    public function map(): ?array
    {
        $this->field['choices'] = $this->structureTerms(
            $this->getTermsFromTaxonomy($this->field)
        );

        $type   = $this->field['field_type'] ?? 'select';
        $mapper = $this->mapClasses[$type] ?? SelectFieldMapper::class;

        if ($type === 'multi_select') {
            $this->field['multiple'] = true;
        }

        return (new $mapper($this->field, $this->wpService, $this->lang))->map();
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