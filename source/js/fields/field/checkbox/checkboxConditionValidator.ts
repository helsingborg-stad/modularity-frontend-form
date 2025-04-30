class CheckboxConditionValidator implements ConditionValidatorInterface {
    private parent: CheckboxInterface|null = null;

    public init(parent: CheckboxInterface): void {
        this.parent = parent;
    }

    public validate(condition: Condition): boolean {
        const selected = this.parent?.getSelectedChoices() ?? [];

        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
                return selected.includes(condition.value);
            case '!=':
            case '!==':
                return !selected.includes(condition.value);
            case '==empty':
                return selected.length === 0;
            case '!=empty':
                return selected.length > 0;
            case '>':
                return selected.some(selectedValue => Number(selectedValue) > Number(condition.value));
            case '<':
                return selected.some(selectedValue => Number(selectedValue) < Number(condition.value));
            default:
                return false;
        }
    }
}

export default CheckboxConditionValidator;