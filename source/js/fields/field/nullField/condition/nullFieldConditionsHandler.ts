class NullFieldConditionsHandler implements ConditionsHandlerInterface {
	constructor(private conditions: ConditionInterface[]) {}

	public init(
		parent: FieldInterface,
		conditionsBuilder: ConditionBuilderInterface,
	): void {
		// No implementation needed for NullField
	}

	public getConditions(): ConditionInterface[] {
		return [];
	}

	public validate(): boolean {
		return true;
	}

	public getIsDisabled(): boolean {
		return false;
	}

	public addValueChangeListener(field: FieldInterface): void {
		return;
	}

	public checkConditions(): void {
		return;
	}
}

export default NullFieldConditionsHandler;
