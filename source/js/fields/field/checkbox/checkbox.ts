class Checkbox implements CheckboxInterface {
    constructor(
        private field: HTMLElement,
        private choices: NodeListOf<HTMLInputElement>,
        private name: string,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.conditionValidator.init(this);
        this.validator.init(this);
        this.listenForChanges();
    }

    public getName(): string {
        return this.name;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.conditionValidator;
    }

    public getChoices(): NodeListOf<HTMLInputElement> {
        return this.choices;
    }

    public getSelectedChoices(): string[] {
        return [...this.getChoices()].filter(choice => choice.checked).map(choice => choice.value);
    }

    public getField(): HTMLElement {
        return this.field;
    }

    private listenForChanges(): void {
        this.choices.forEach(choice => {
            choice.addEventListener('change', () => {
                this.conditionsHandler.checkConditions();
                this.validator.valueChangeListener();
            });
        });
    }
}

export default Checkbox;