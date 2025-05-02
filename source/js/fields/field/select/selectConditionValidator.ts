class SelectConditionValidator implements ConditionValidatorInterface {
    private parent: SelectInterface|null = null;

    public init(parent: SelectInterface): void {
        this.parent = parent;
    }

    public validate(condition: any): boolean {
        const selected: string[] = this.parent?.getSelectedOptions() || [];

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
            default:
                return false;
        }
    }
}

export default SelectConditionValidator;