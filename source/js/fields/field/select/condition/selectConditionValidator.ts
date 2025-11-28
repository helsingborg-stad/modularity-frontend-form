class SelectConditionValidator implements ConditionValidatorInterface {
	private parent: SelectInterface | null = null;

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
				return selected.includes(condition.value);
			case "!=":
			case "!==":
			case "!=contains":
				return !selected.includes(condition.value);
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
