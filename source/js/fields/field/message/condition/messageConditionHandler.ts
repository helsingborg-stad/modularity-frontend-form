class MessageConditionsHandler implements ConditionsHandlerInterface {
	private parent: FieldInterface|null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
	}

	private updateDisabled(disabled: boolean): void {
		if (this.parent && this.isDisabled !== disabled) {
			this.isDisabled = disabled;

            this.parent.getFieldContainer().classList.toggle('u-display--none', disabled);
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
		return;
    }

	public checkConditions(): void {
		return;
	}
}

export default MessageConditionsHandler;