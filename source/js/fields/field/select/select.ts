class Select implements SelectInterface {
    private required: boolean = false;
    constructor(
        private field: HTMLElement,
        private select: HTMLSelectElement,
        private options: NodeListOf<HTMLOptionElement>,
        private name: string,
        private selectValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getSelect().required;
        this.conditionsHandler.init(this, conditionBuilder);
        this.selectValidator.init(this);
        this.validator.init(this);
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

    public getValidator(): FieldValidatorInterface {
        return this.validator;
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

    public hasValue(): boolean {
        return this.getSelectedOptions().length > 0;
    }

    public isRequired(): boolean {
        return this.required;
    }

    public getSelectedOptions(): string[] {
        return [...this.getOptions()].filter(option => option.selected).map(option => option.value);
    }

    public listenForChanges(): void {
        this.getSelect().addEventListener('change', () => {
            this.getValidator().validate();
            this.conditionsHandler.checkConditions();
        });

        this.getSelect().addEventListener('blur', () => {
            this.getValidator().validate();
        });
    }
}

export default Select;