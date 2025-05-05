class RadioConditionValidator implements ConditionValidatorInterface {
    private parent: RadioInterface|null = null;

    public init(parent: RadioInterface): void {
        this.parent = parent;
    }

    public validate(condition: Condition): boolean {
        const selected = this.parent?.getSelectedChoice() ?? '';

        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
                return selected === condition.value;
            case '!=':
            case '!==':
                return selected !== condition.value;
            case '==empty':
                return Number(selected) === 0;
            case '!=empty':
                return Number(selected) > 0;
            default:
                return false;
        }
    }
}

export default RadioConditionValidator;