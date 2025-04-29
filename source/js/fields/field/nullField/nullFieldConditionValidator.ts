class NullFieldConditionValidator implements ConditionValidatorInterface {
    public init(parent: FieldInterface): void {
        // No implementation needed for NullField
    }

    public validate(condition: Condition): boolean {
        
        return false;
    }
}

export default NullFieldConditionValidator;