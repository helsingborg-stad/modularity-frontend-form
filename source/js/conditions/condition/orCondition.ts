class OrCondition implements ConditionInterface {
    constructor(private condition: Condition) {
    }

    public validate(): boolean {
        return true;
    }

    public getConditionFieldNames(): string[] {
        return [this.condition.field];
    }
}

export default OrCondition;