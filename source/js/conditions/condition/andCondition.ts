class AndCondition implements ConditionInterface {
	constructor(private conditions: Condition[]) {}

	public validate(): boolean {
		for (const condition of this.conditions) {
			if (!condition.class) {
				continue;
			}

			if (condition.class.getConditionsHandler().getIsDisabled()) {
				return false;
			}

			if (!condition.class.getConditionValidator().validate(condition)) {
				return false;
			}
		}

		return true;
	}

	public getConditions(): Condition[] {
		return this.conditions;
	}

	public getConditionFieldNames(): string[] {
		return this.conditions.flatMap((condition) =>
			condition.field ? [condition.field] : [],
		);
	}
}

export default AndCondition;
