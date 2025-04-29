import { TilesHelper } from "../../../../../dist/js-init.0f85b13b34f29a8d87e4";

class CheckboxConditionsHandler implements ConditionsHandlerInterface {
	private fieldsObject: FieldsObject = {};
	private parent: CheckboxInterface | null = null;
	private conditions: ConditionInterface[] = [];
	private isDisabled: boolean = false;

	constructor(private unstructuredConditions: any) {
	}

	public init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void {
		this.parent = parent as CheckboxInterface;
		this.conditions = conditionsBuilder.build(this.unstructuredConditions);
		this.setValueChangeListener();
	}

	private updateDisabled(disabled: boolean): void {
		if (this.isDisabled !== disabled) {
			this.isDisabled = disabled;

			this.parent?.getChoices().forEach((checkbox, index) => {
				if (index === 0) {
					checkbox.dispatchEvent(new Event('change'));
					this.parent?.getField().classList.toggle('u-display--none', disabled)
				}

				checkbox.disabled = disabled;
			});
		}
	}

	public validate(): void {
		console.log(this.parent?.getName())
		let isValid: boolean = false;
		for (const condition of this.getConditions()) {
			if (condition.validate()) {
				isValid = true;
				break;
			}
		}

		this.updateDisabled(!isValid);
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