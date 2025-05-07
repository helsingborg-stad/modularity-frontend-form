<?php 

namespace ModularityFrontendForm\FieldMapping\Mapper;

class ConditionalLogicMapper
{
    public function __construct(protected array $field, protected string $subkey){}

    public static function getInstance(array $field, string $subkey): static
    {
        return new static($field, $subkey);
    }

    /**
     * Map the conditional logic for a field.
     *
     * @return string|null The mapped conditional logic as a JSON string, or null if not applicable.
     * 
     * @throws \JsonException If the conditional logic cannot be encoded as JSON.
     */
    public function map(): ?string
    {
        $conditionalLogic = $this->field[$this->subkey]['conditionalLogic'] ?? null;

        if (empty($conditionalLogic)) {
            return null;
        }

        if (is_string($conditionalLogic) && json_decode($conditionalLogic, true) !== null) {
            return $conditionalLogic;
        }

        if (is_array($conditionalLogic)) {
            $conditionalLogic = json_encode($conditionalLogic, JSON_THROW_ON_ERROR);
        }

        throw new \JsonException('Invalid conditional logic format');
    }
}