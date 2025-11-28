class FileConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: BasicInterface | null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {}

	public init(
		parent: BasicInterface,
		conditionsBuilder: ConditionBuilderInterface,
	): void {
		this.parent = parent;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
	}

	private updateDisabled(disable: boolean): void {
		if (this.parent && this.isDisabled !== disable) {
			this.isDisabled = disable;

			this.parent
				.getFieldContainer()
				.classList.toggle("u-display--none", disable);
			this.parent.getField().disabled = disable;

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

export default FileConditionsHandler;
