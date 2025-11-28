class BasicConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: BasicInterface | SelectInterface | TextAreaInterface | null =
		null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {}

	public init(
		parent: BasicInterface | SelectInterface | TextAreaInterface,
		conditionsBuilder: ConditionBuilderInterface,
	): void {
		this.parent = parent;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
	}

	private updateDisabled(disabled: boolean): void {
		if (this.parent && this.isDisabled !== disabled) {
			this.isDisabled = disabled;

			this.parent
				.getFieldContainer()
				.classList.toggle("u-display--none", disabled);
			this.parent.getField().disabled = disabled;

			this.checkConditions();
		}
	}

	public validate(): void {
		let isValid: boolean = false;

		for (const condition of this.getConditions()) {
			if (condition.validate()) {
				isValid = true;
				break;
			}
		}

		this.updateDisabled(!isValid);
	}

	public getIsDisabled(): boolean {
		return this.isDisabled;
	}

	public getConditions(): ConditionInterface[] {
		return this.conditions;
	}

	public addValueChangeListener(field: FieldInterface): void {
		this.fieldsObject[field.getName()] = field;
	}

	public checkConditions(): void {
		for (const fieldName in this.fieldsObject) {
			this.fieldsObject[fieldName].getConditionsHandler().validate();
		}
	}
}

export default BasicConditionsHandler;
