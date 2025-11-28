class GoogleMapConditionValidator implements ConditionValidatorInterface {
	private parent: GoogleMapInterface | null = null;

	public init(parent: GoogleMapInterface): void {
		this.parent = parent;
	}

	public validate(condition: any): boolean {
		const value = this.parent?.getOpenstreetmap().hasPlaceData() ? true : false;
		switch (condition.operator) {
			case "==":
			case "=":
			case "===":
			case "!=empty":
			case "==contains":
				return value === true;
			case "!=":
			case "!==":
			case "==empty":
			case "!=contains":
				return value === false;
			default:
				console.error("Invalid operator:", condition.operator);
				return false;
		}
	}
}

export default GoogleMapConditionValidator;
