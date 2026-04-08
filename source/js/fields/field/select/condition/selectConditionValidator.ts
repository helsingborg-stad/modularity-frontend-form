class SelectConditionValidator implements ConditionValidatorInterface {
	private parent: SelectInterface | null = null;

	private getConditionValues(condition: any): string[] {
		return Array.isArray(condition.value) ? condition.value : [condition.value];
	}

	public init(parent: SelectInterface): void {
		this.parent = parent;
	}

	public validate(condition: any): boolean {
		const selected: string[] = this.parent?.getSelectedOptions() || [];

		switch (condition.operator) {
			case "==":
			case "=":
			case "===":
			case "==contains":
				return this.getConditionValues(condition).some((conditionValue) =>
					selected.includes(conditionValue),
				);
			case "!=":
			case "!==":
			case "!=contains":
				return this.getConditionValues(condition).every(
					(conditionValue) => !selected.includes(conditionValue),
				);
			case "==empty":
				return selected.length === 0;
			case "!=empty":
				return selected.length > 0;
			default:
				console.error("Invalid operator:", condition.operator);
				return false;
		}
	}
}

export default SelectConditionValidator;
