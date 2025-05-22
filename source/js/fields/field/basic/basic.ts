class Basic implements BasicInterface {
    private required: boolean = false;
    private hasBeenFilled: boolean = false;
    constructor(
        private field: HTMLElement,
        private input: HTMLInputElement,
        private name: string,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getInput().hasAttribute('required');
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

    public getField(): HTMLElement {
        return this.field;
    }

    public hasValue(): boolean {
        return this.getInput().value.length > 0;
    }

    public isRequired(): boolean {
        return this.required;
    }

    public getInput(): HTMLInputElement {
        return this.input;
    }

    private listenForChanges(): void {
        this.getInput().addEventListener('input', () => {
            this.getConditionsHandler().checkConditions();

            this.hasBeenFilled && this.getValidator().validate();
        });

        this.getInput().addEventListener('blur', () => {
            this.hasBeenFilled = true;
            this.getValidator().validate();
        });
    }
}

export default Basic;