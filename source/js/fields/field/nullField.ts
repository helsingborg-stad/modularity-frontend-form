class NullField implements FieldInterface {
    constructor(
        private field: HTMLElement,
        type: string,
        private name: string,
        private conditions: ConditionInterface[]
    ) {
        console.error(`Field type "${type}" is not implemented.`);
    }

    public getName(): string {
        return '';
    }

    public validateConditionals(): boolean {
        return true;
    }

    public addValueChangeListener(field: FieldInterface): void {
        return;
    }

    public getConditions(): ConditionInterface[] {
        return this.conditions;
    }
}

export default NullField;