// TODO: This should probably be renamed to input.ts since it is used for all input types

class Basic implements BasicInterface {
	private required: boolean = false;
	private hasBeenFilled: boolean = false;
	constructor(
		private field: HTMLElement,
		private input: HTMLInputElement,
		private name: string,
		private conditionValidator: ConditionValidatorInterface,
		private conditionsHandler: ConditionsHandlerInterface,
		private validator: FieldValidatorInterface,
	) {}

	public init(conditionBuilder: ConditionBuilderInterface): void {
		this.required = this.getField().hasAttribute("required");
		this.conditionsHandler.init(this, conditionBuilder);
		this.conditionValidator.init(this);
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
		return this.conditionValidator;
	}

	public getValidator(): FieldValidatorInterface {
		return this.validator;
	}

	public getFieldContainer(): HTMLElement {
		return this.field;
	}

	public hasValue(): boolean {
		return this.getField().value.length > 0;
	}

	public isRequired(): boolean {
		return this.required;
	}

	public getField(): HTMLInputElement {
		return this.input;
	}

	private listenForChanges(): void {
		this.getField().addEventListener("input", () => {
			this.getConditionsHandler().checkConditions();

			this.hasBeenFilled && this.getValidator().validate();
		});

		this.getField().addEventListener("blur", () => {
			this.hasBeenFilled = true;
			this.getValidator().validate();
		});
	}
}

export default Basic;
