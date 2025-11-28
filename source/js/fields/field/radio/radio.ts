class Radio implements RadioInterface {
	private required: boolean = false;
	constructor(
		private field: HTMLElement,
		private choices: NodeListOf<HTMLInputElement>,
		private name: string,
		private radioValidator: ConditionValidatorInterface,
		private conditionsHandler: ConditionsHandlerInterface,
		private validator: FieldValidatorInterface,
	) {}

	public init(conditionBuilder: ConditionBuilderInterface): void {
		this.required = this.getFieldContainer().hasAttribute("data-js-required");
		this.conditionsHandler.init(this, conditionBuilder);
		this.radioValidator.init(this);
		this.validator.init(this);
		this.listenForChanges();
	}

	public getName(): string {
		return this.name;
	}

	public getConditionsHandler(): ConditionsHandlerInterface {
		return this.conditionsHandler;
	}

	public getConditionValidator(): ConditionValidatorInterface {
		return this.radioValidator;
	}

	public getValidator(): FieldValidatorInterface {
		return this.validator;
	}

	public getChoices(): NodeListOf<HTMLInputElement> {
		return this.choices;
	}

	public hasValue(): boolean {
		return this.getSelectedChoice().length > 0;
	}

	public isRequired(): boolean {
		return this.required;
	}

	public getSelectedChoice(): string {
		const selectedChoice = [...this.getChoices()].find(
			(choice) => choice.checked,
		);
		return selectedChoice ? selectedChoice.value : "";
	}

	public getFieldContainer(): HTMLElement {
		return this.field;
	}

	private listenForChanges(): void {
		this.choices.forEach((choice) => {
			choice.addEventListener("change", () => {
				this.conditionsHandler.checkConditions();
				this.validator.validate();
			});
		});
	}
}

export default Radio;
