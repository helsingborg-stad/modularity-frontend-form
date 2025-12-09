import BasicValueLoader from '../basic/load/basicValueLoader';
import TextAreaValueLoader from './load/textAreaValueLoader';

class TextArea implements TextAreaInterface {
	private required: boolean = false;
	private hasBeenFilled: boolean = false;
	constructor(
		private field: HTMLElement,
		private input: HTMLTextAreaElement,
		private name: string,
		private conditionValidator: ConditionValidatorInterface,
		private conditionsHandler: ConditionsHandlerInterface,
		private validator: FieldValidatorInterface,
		private loader: TextAreaValueLoaderInterface = new TextAreaValueLoader(),
	) {}

	public init(conditionBuilder: ConditionBuilderInterface): void {
		this.required = this.getField().hasAttribute('required');
		this.conditionsHandler.init(this, conditionBuilder);
		this.conditionValidator.init(this);
		this.validator.init(this);
		this.loader.init(this);

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

	public getField(): HTMLTextAreaElement {
		return this.input;
	}

	public getValueLoader(): TextAreaValueLoaderInterface {
		return this.loader;
	}

	private listenForChanges(): void {
		this.getField().addEventListener('input', () => {
			this.getConditionsHandler().checkConditions();

			this.hasBeenFilled && this.getValidator().validate();
		});

		this.getField().addEventListener('blur', () => {
			this.hasBeenFilled = true;
			this.getValidator().validate();
		});
	}
}

export default TextArea;
