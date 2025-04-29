class OrCondition implements ConditionInterface {
    constructor(private condition: Condition) {
    }

    public validate(): boolean {
        if (!this.condition.class) {
            return true;
        }

        return this.condition.class.getConditionsHandler().getIsDisabled() ?
            false :
            (this.condition.class.getConditionValidator().validate(this.condition) ?? true);
    }

    public getConditions(): Condition[] {
        return [this.condition];
    }

    public getConditionFieldNames(): string[] {
        return [this.condition.field];
    }
}

export default OrCondition;