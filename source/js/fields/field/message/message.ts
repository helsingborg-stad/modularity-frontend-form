class Message implements FieldInterface {
    constructor(
        private field: HTMLElement,
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
        return true;
    }

    public isRequired(): boolean {
        return false;
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default Message;