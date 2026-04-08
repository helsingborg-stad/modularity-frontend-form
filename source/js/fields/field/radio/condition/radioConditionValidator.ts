class RadioConditionValidator implements ConditionValidatorInterface {
	private parent: RadioInterface | null = null;

	private getConditionValues(condition: Condition): string[] {
		return Array.isArray(condition.value) ? condition.value : [condition.value];
	}

	public init(parent: RadioInterface): void {
		this.parent = parent;
	}

	public validate(condition: Condition): boolean {
		const selected = this.parent?.getSelectedChoice() ?? "";

		switch (condition.operator) {
			case "==":
			case "=":
			case "===":
			case "==contains":
				return this.getConditionValues(condition).includes(selected);
			case "!=":
			case "!==":
			case "!=contains":
				return !this.getConditionValues(condition).includes(selected);
			case "==empty":
				return Number(selected) === 0;
			case "!=empty":
				return Number(selected) > 0;
			default:
				console.error("Invalid operator:", condition.operator);
				return false;
		}
	}
}

export default RadioConditionValidator;
