class RepeaterConditionValidator implements ConditionValidatorInterface {
	private parent: RepeaterInterface | null = null;

	public init(parent: RepeaterInterface): void {
		this.parent = parent;
	}

	public validate(condition: any): boolean {
		const rowCount = this.parent?.getRowCount() ?? 0;

		switch (condition.operator) {
			case "==empty":
				return rowCount === 0;
			case "!=empty":
				return rowCount !== 0;
			case ">":
				return rowCount > Number(condition.value);
			case "<":
				return rowCount < Number(condition.value);
			default:
				console.error("Invalid operator:", condition.operator);
				return false;
		}
	}
}

export default RepeaterConditionValidator;
