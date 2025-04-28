class NullFieldConditionValidator implements ConditionValidatorInterface {
    public validate(condition: Condition): boolean {
        
        return false;
    }
}

export default NullFieldConditionValidator;