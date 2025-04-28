class CheckboxConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: CheckboxInterface | null = null;
	private conditions: ConditionInterface[] = [];

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent as CheckboxInterface;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
		this.setValueChangeListener();
	}

	public validate(): boolean {
		this.getConditions().forEach((condition) => {
			condition.validate();
		})

		return false;
	}

	public getConditions(): ConditionInterface[] {
		return this.conditions;
	}

    public addValueChangeListener(field: FieldInterface): void {
		this.fieldsObject[field.getName()] = field;
    }

	private setValueChangeListener(): void {
		this.parent?.getChoices().forEach((checkbox) => {
			checkbox.addEventListener('change', () => {
				for (const fieldName in this.fieldsObject) {
					this.fieldsObject[fieldName].getConditionsHandler().validate();
				}
			});
		});
	}
}

export default CheckboxConditionsHandler;