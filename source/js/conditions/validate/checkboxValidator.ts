class CheckboxValidator implements ConditionValidatorInterface {
    public validate(condition: Condition): boolean {
        // if (condition.class) {
        //     const field = condition.class.getField(condition.field);
        //     if (field && field.getType() === 'checkbox') {
        //         return true;
        //     }
        // }
        return false;
    }
}

export default CheckboxValidator;