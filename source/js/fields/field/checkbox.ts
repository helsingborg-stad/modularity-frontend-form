class Checkbox implements FieldInterface {
    private valueChangeListeners: FieldInterface[] = [];

    constructor(
        private field: HTMLElement,
        private choices: NodeListOf<HTMLInputElement>,
        private name: string,
        private conditions: ConditionInterface[]
    ) {

    }

    private changeListener() {
        
    }

    public getName(): string {
        return this.name;
    }

    public validateConditionals(): boolean {
        return true;
    }

    public addValueChangeListener(field: FieldInterface): void {
        this.valueChangeListeners.push(field);
    }

    public getConditions(): ConditionInterface[] {
        return this.conditions;
    }
}

export default Checkbox;