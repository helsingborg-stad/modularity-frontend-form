class NullCondition implements ConditionInterface {
    public validate(): boolean {
        return true;
    }

    public getConditions(): Condition[] {
        return [];
    }


    public getConditionFieldNames(): string[] {
        return [];
    }
}

export default NullCondition;