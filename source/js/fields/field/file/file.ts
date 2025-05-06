class File implements FileInterface {
    constructor(
        private field: HTMLElement,
        private input: HTMLInputElement,
        private name: string,
        private inputValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.inputValidator.init(this);
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.inputValidator;
    }

    public getInput(): HTMLInputElement {
        return this.input;
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default File;