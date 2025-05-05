class RadioConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: RadioInterface | null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: RadioInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent;
		console.log(this.unstructuredConditions);
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
		this.setValueChangeListener();
	}

	private updateDisabled(disabled: boolean): void {
		if (this.isDisabled !== disabled) {
			this.isDisabled = disabled;

			this.parent?.getChoices().forEach((checkbox, index) => {
				if (index === 0) {
					this.parent?.getField().classList.toggle('u-display--none', disabled)
				}
				
				checkbox.disabled = disabled;
			});

			this.dispatchUpdateEvent();
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

	public dispatchUpdateEvent(): void {
		const choice = this.parent?.getChoices()[0];

		if (choice) {
			choice.dispatchEvent(new Event('change'));
		}
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

	private setValueChangeListener(): void {
		this.parent?.getChoices().forEach((radio) => {
			radio.addEventListener('change', () => {
				for (const fieldName in this.fieldsObject) {
					this.fieldsObject[fieldName].getConditionsHandler().validate();
				}
			});
		});
	}
}

export default RadioConditionsHandler;