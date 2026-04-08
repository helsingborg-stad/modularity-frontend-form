class BasicConditionValidator implements ConditionValidatorInterface {
	private parent: BasicInterface | null = null;

	private getConditionValues(condition: any): string[] {
		return Array.isArray(condition.value) ? condition.value : [condition.value];
	}

	public init(parent: BasicInterface): void {
		this.parent = parent;
	}

	public validate(condition: any): boolean {
		const value = this.parent?.getField().value ?? "";

		switch (condition.operator) {
			case "==":
			case "=":
			case "===":
				return value === condition.value;
			case "!=":
			case "!==":
				return value !== condition.value;
			case "==empty":
				return value.length === 0;
			case "!=empty":
				return value.length > 0;
			case "==contains":
				return this.getConditionValues(condition).some((conditionValue) =>
					value.includes(conditionValue),
				);
			case "!=contains":
				return this.getConditionValues(condition).every(
					(conditionValue) => !value.includes(conditionValue),
				);
			case ">":
				return Number(value) > Number(condition.value);
			case "<":
				return Number(value) < Number(condition.value);
			default:
				console.error("Invalid operator:", condition.operator);
				return false;
		}
	}
}

export default BasicConditionValidator;
