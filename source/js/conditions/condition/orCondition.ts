class OrCondition implements ConditionInterface {
    constructor(private condition: Condition) {
    }

    public validate(): boolean {
        // Implement validation logic
        return true;
    }

    public getConditions(): Condition[] {
        return [this.condition];
    }

    public getConditionFieldNames(): string[] {
        return [this.condition.field];
    }
}

export default OrCondition;