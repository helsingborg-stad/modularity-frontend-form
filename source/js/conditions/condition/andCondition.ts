class AndCondition implements ConditionInterface {
    constructor(private conditions: Condition[]) {
    }

    public validate(): boolean {
        return true;
    }

    public getConditionFieldNames(): string[] {
        return this.conditions.flatMap(condition => condition.field ? [condition.field] : []);
    }
}

export default AndCondition;