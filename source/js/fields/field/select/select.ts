class Select implements SelectInterface {
    constructor(
        private field: HTMLElement,
        private select: HTMLSelectElement,
        private options: NodeListOf<HTMLOptionElement>,
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

    public getSelect(): HTMLSelectElement {
        return this.select;
    }

    public getOptions(): NodeListOf<HTMLOptionElement> {
        return this.options;
    }
}

export default Select;