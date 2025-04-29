class AndCondition implements ConditionInterface {
    constructor(private conditions: Condition[]) {
    }

    public validate(): boolean {
        let isValid: boolean[] = [];
        // TODO: No need to check all, if one is false it is false.
        this.conditions.forEach(condition => {
            if (!condition.class) {
                return;
            }

            isValid.push(condition.class.getConditionValidator().validate(condition));
        });

        if (isValid.includes(false)) {
            return false;
        }

        return true;
    }

    public getConditions(): Condition[] {
        return this.conditions;
    }

    public getConditionFieldNames(): string[] {
        return this.conditions.flatMap(condition => condition.field ? [condition.field] : []);
    }
}

export default AndCondition;