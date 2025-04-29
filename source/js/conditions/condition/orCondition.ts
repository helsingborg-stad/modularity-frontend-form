class OrCondition implements ConditionInterface {
    constructor(private condition: Condition) {
    }

    public validate(): boolean {
        if (!this.condition.class) {
            return true;
        }

        if (this.condition.class.getConditionsHandler().getIsDisabled()) {
            return false;
        }

        return this.condition.class.getConditionValidator().validate(this.condition);
    }

    public getConditions(): Condition[] {
        return [this.condition];
    }

    public getConditionFieldNames(): string[] {
        return [this.condition.field];
    }
}

export default OrCondition;