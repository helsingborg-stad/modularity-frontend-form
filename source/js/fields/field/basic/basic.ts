class Basic implements BasicInterface {
    constructor(
        private field: HTMLElement,
        private input: HTMLInputElement,
        private name: string,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.conditionValidator.init(this);
        this.listenForChanges();
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.conditionValidator;
    }

    public getField(): HTMLElement {
        return this.field;
    }

    public getInput(): HTMLInputElement {
        return this.input;
    }

    private listenForChanges(): void {
        this.getInput().addEventListener('input', () => {
            this.conditionsHandler.checkConditions();
        });
    }
}

export default Basic;