class FileConditionValidator implements ConditionValidatorInterface {
    private parent: BasicInterface|null = null;

    public init(parent: BasicInterface): void {
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
                console.error('Invalid operator:', condition.operator);
                return false;
        }

    }
}

export default FileConditionValidator;