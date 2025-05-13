class Select implements SelectInterface {
    constructor(
        private field: HTMLElement,
        private select: HTMLSelectElement,
        private options: NodeListOf<HTMLOptionElement>,
        private name: string,
        private selectValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.selectValidator.init(this);
        this.listenForChanges();
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.selectValidator;
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

    public getSelectedOptions(): string[] {
        return [...this.getOptions()].filter(option => option.selected).map(option => option.value);
    }

    public listenForChanges(): void {
        this.getSelect().addEventListener('change', () => {
            this.conditionsHandler.checkConditions();
        });
    }
}

export default Select;