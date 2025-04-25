class NullCondition implements ConditionInterface {
    public validate(): boolean {
        return true;
    }

    public getConditionFieldNames(): string[] {
        return [];
    }
}

export default NullCondition;