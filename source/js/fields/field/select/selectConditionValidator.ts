class SelectConditionValidator implements ConditionValidatorInterface {
    private parent: SelectInterface|null = null;

    public init(parent: SelectInterface): void {
        this.parent = parent;
    }

    public validate(condition: any): boolean {
        const value = this.parent?.getSelect().value ?? '';

        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
                return value === condition.value;
            case '!=':
            case '!==':
                return value !== condition.value;
            case '==empty':
                return value.length === 0;
            case '!=empty':
                return value.length > 0;
            default:
                return false;
        }
    }
}

export default SelectConditionValidator;