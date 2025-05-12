class FileConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: BasicInterface|null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: BasicInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
		this.setValueChangeListener();
	}

	private updateDisabled(disable: boolean): void {
		if (this.parent && this.isDisabled !== disable) {
			this.isDisabled = disable;

            this.parent.getField().classList.toggle('u-display--none', disable);
            this.parent.getInput().disabled = disable;

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
		if (this.parent?.getInput()) {
			this.parent.getInput().dispatchEvent(new Event('change'));
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
        this.parent?.getInput().addEventListener('change', () => {
            for (const fieldName in this.fieldsObject) {
                this.fieldsObject[fieldName].getConditionsHandler().validate();
            }
        });
	}
}

export default FileConditionsHandler;