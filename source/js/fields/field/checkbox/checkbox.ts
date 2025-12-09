import CheckboxValueLoader from './load/checkboxValueLoader';

class Checkbox implements CheckboxInterface {
	private required: boolean = false;
	constructor(
		private field: HTMLElement,
		private choices: NodeListOf<HTMLInputElement>,
		private name: string,
		private conditionValidator: ConditionValidatorInterface,
		private conditionsHandler: ConditionsHandlerInterface,
		private validator: FieldValidatorInterface,
		private loader: CheckboxValueLoaderInterface = new CheckboxValueLoader(),
	) {}

	public init(conditionBuilder: ConditionBuilderInterface): void {
		this.required = this.getFieldContainer().hasAttribute('data-js-required');
		this.conditionsHandler.init(this, conditionBuilder);
		this.conditionValidator.init(this);
		this.validator.init(this);
		this.loader.init(this);
		this.listenForChanges();
	}

	public getName(): string {
		return this.name;
	}

	public getValidator(): FieldValidatorInterface {
		return this.validator;
	}

	public getConditionsHandler(): ConditionsHandlerInterface {
		return this.conditionsHandler;
	}

	public getConditionValidator(): ConditionValidatorInterface {
		return this.conditionValidator;
	}

	public getChoices(): NodeListOf<HTMLInputElement> {
		return this.choices;
	}

	public isRequired(): boolean {
		return this.required;
	}

	public hasValue(): boolean {
		return this.getSelectedChoices().length > 0;
	}

	public getValueLoader(): CheckboxValueLoaderInterface {
		return this.loader;
	}

	public getSelectedChoices(): string[] {
		return [...this.getChoices()].filter((choice) => choice.checked).map((choice) => choice.value);
	}

	public getFieldContainer(): HTMLElement {
		return this.field;
	}

	private listenForChanges(): void {
		this.choices.forEach((choice) => {
			choice.addEventListener('change', () => {
				this.conditionsHandler.checkConditions();
				this.validator.validate();
			});
		});
	}
}

export default Checkbox;
