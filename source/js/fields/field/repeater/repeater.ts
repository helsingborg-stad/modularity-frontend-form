class Repeater implements RepeaterInterface {
    constructor(
        private field: HTMLElement,
        private name: string,
        private repeaterUI: RepeaterUIInterface,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.conditionValidator.init(this);
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

    public getRowCount(): number {
        return this.repeaterUI.getRowCount();
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default Repeater;