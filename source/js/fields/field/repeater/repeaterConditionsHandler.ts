class RepeaterConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: RepeaterInterface|null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: RepeaterInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
	}

	private updateDisabled(disabled: boolean): void {
		if (this.parent && this.isDisabled !== disabled) {
			this.isDisabled = disabled;

            this.parent.getField().classList.toggle('u-display--none', disabled);
            // TODO: Do we need to do something here?
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
		// if (this.parent?.getInput()) {
		// 	this.parent.getInput().dispatchEvent(new Event('input'));
		// }
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
        // this.parent?.getInput().addEventListener('input', () => {
        //     for (const fieldName in this.fieldsObject) {
        //         this.fieldsObject[fieldName].getConditionsHandler().validate();
        //     }
        // });
	}
}

export default RepeaterConditionsHandler;