class NullField implements FieldInterface {
    constructor(
        private field: HTMLElement,
        type: string,
        private name: string,
        private nullFieldConditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface

    ) {
        console.error(`Field type "${type}" is not implemented.`);
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.nullFieldConditionValidator.init(this);
        this.validator.init(this);
    }

    public getFieldContainer(): HTMLElement {
        return this.field;
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public isRequired(): boolean {
        return false;
    }

    public hasValue(): boolean {
        return true;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.nullFieldConditionValidator;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }
}

export default NullField;