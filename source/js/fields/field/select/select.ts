class Select implements SelectInterface {
    private required: boolean = false;
    constructor(
        private field: HTMLElement,
        private select: HTMLSelectElement,
        private options: NodeListOf<HTMLOptionElement>,
        private name: string,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getField().required;
        this.conditionsHandler.init(this, conditionBuilder);
        this.conditionValidator.init(this);
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
        return this.conditionValidator;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    public getFieldContainer(): HTMLElement {
        return this.field;
    }

    public getField(): HTMLSelectElement {
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
        this.getField().addEventListener('change', () => {
            this.getValidator().validate();
            this.conditionsHandler.checkConditions();
        });

        this.getField().addEventListener('blur', () => {
            this.getValidator().validate();
        });
    }
}

export default Select;