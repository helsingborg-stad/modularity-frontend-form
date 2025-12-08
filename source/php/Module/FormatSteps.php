<?php

namespace ModularityFrontendForm\Module;

use AcfService\AcfService;
use WpService\WpService;

use ModularityFrontendForm\Config\ConfigInterface;

class FormatSteps {

    /**
     * Constructor
     * @param WpService $wpService
     * @param AcfService $acfService
     * @param ConfigInterface $config
     * @param FieldFormatterResolverInterface[] $FieldFormatterResolverInterfaces
     */
    public function __construct(
        private array $fieldFormatterResolver
    ){}

    /**
     * Formats the steps to be used in the frontend.
     *
     * @param array $steps The steps to format.
     * 
     * @return array The formatted steps.
     */
    public function formatSteps(array $steps) 
    {
        $formattedSteps = [];
        foreach ($steps as $key => $step) {
            $formattedSteps[$key]['title'] = $step['formStepTitle'] ?? null;
            $formattedSteps[$key]['description'] = $step['formStepContent'] ?? null;
            $formattedSteps[$key]['fields'] = $this->formatStep($step);
        }

        return $formattedSteps;
    }

    /**
     * Formats a single step to be used in the frontend.
     *
     * @param array $unformattedStep The unformatted step to format.
     * 
     * @return array The formatted step.
     */
    public function formatStep(array $unformattedStep): array
    {
        $fieldGroups = $unformattedStep['formStepGroup'] ?? [];

        $formattedStep = [];
        foreach ($fieldGroups as $fieldGroup) {
            foreach ($this->fieldFormatterResolver as $resolver) {
                $result = $resolver->resolve($fieldGroup);
                if ($result) {
                    $formattedStep = array_merge($formattedStep, $result);
                    break;
                }
            }
        }

        return $formattedStep;
    }
}