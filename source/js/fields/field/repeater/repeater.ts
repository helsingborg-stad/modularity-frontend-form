class Repeater implements RepeaterInterface {
    private required: boolean = false;
    constructor(
        private field: HTMLElement,
        private name: string,
        private repeaterUI: RepeaterUIInterface,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.field.hasAttribute('data-js-required');
        this.repeaterUI.init(this, conditionBuilder);
        this.conditionsHandler.init(this, conditionBuilder);
        this.conditionValidator.init(this);
        this.validator.init(this);
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

    public hasValue(): boolean {
        return this.repeaterUI.getRowCount() > 0;
    }

    public getRowCount(): number {
        return this.repeaterUI.getRowCount();
    }

    public isRequired(): boolean {
        return this.required;
    }
    
    public getField(): HTMLElement {
        return this.field;
    }
}

export default Repeater;