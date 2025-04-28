class NullField implements FieldInterface {
    constructor(
        private field: HTMLElement,
        type: string,
        private name: string,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
        console.error(`Field type "${type}" is not implemented.`);
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
    }

    public getField(): HTMLElement {
        return this.field;
    }

    public getName(): string {
        return '';
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }
}

export default NullField;