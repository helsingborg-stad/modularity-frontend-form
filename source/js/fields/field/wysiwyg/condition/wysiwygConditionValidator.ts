class WysiwygConditionValidator implements ConditionValidatorInterface {
    private parent: WysiwygInterface|null = null;

    public init(parent: WysiwygInterface): void {
        this.parent = parent;
    }

    public validate(condition: Condition): boolean {
        const value = this.parent?.getEditor().hasContent() ? true : false;
        switch (condition.operator) {
            case '==':
            case '=':
            case '===':
            case '!=empty':
            case '==contains':
                return value === true;
            case '!=':
            case '!==':
            case '==empty':
            case '!=contains':
                return value === false;
            default:
                console.error('Invalid operator:', condition.operator);
                return false;
        }
    }
}

export default WysiwygConditionValidator;