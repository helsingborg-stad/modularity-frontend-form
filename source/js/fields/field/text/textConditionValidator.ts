class TextConditionValidator implements ConditionValidatorInterface {
    private parent: TextInterface|null = null;

    public init(parent: TextInterface): void {
        this.parent = parent;
    }

    public validate(condition: any): boolean {
        const value = this.parent?.getInput().value ?? '';

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
            case '==contains':
                return value.includes(condition.value);
            case '!=contains':
                return !value.includes(condition.value);
            case '>':
                return Number(value) > Number(condition.value);
            case '<':
                return Number(value) < Number(condition.value);
            default:
                return false;
        }

    }
}

export default TextConditionValidator;