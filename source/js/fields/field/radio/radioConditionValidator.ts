class RadioConditionValidator implements ConditionValidatorInterface {
    private parent: RadioInterface|null = null;

    public init(parent: RadioInterface): void {
        this.parent = parent;
    }

    public validate(condition: Condition): boolean {
        const selected = this.parent?.getSelectedChoice() ?? '';
        console.log(condition, 'selected', selected);

        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
            case '==contains':
                return selected === condition.value;
            case '!=':
            case '!==':
            case '!=contains':
                return selected !== condition.value;
            case '==empty':
                return Number(selected) === 0;
            case '!=empty':
                return Number(selected) > 0;
            default:
                console.error('Invalid operator:', condition.operator);
                return false;
        }
    }
}

export default RadioConditionValidator;