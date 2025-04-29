class Checkbox implements CheckboxInterface {
    constructor(
        private field: HTMLElement,
        private choices: NodeListOf<HTMLInputElement>,
        private name: string,
        private checkboxValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.checkboxValidator.init(this);
    }

    public getName(): string {
        return this.name;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.checkboxValidator;
    }

    public getChoices(): NodeListOf<HTMLInputElement> {
        return this.choices;
    }

    public getSelectedChoices(): string[] {
        return [...this.getChoices()].filter(choice => choice.checked).map(choice => choice.value);
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default Checkbox;