class Text implements TextInterface {
    constructor(
        private field: HTMLElement,
        private input: HTMLInputElement,
        private name: string,
        private textValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.textValidator.init(this);
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.textValidator;
    }

    public getField(): HTMLElement {
        return this.field;
    }

    public getInput(): HTMLInputElement {
        return this.input;
    }
}

export default Text;