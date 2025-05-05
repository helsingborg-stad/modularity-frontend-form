class Radio implements RadioInterface {
    constructor(
        private field: HTMLElement,
        private choices: NodeListOf<HTMLInputElement>,
        private name: string,
        private radioValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.conditionsHandler.init(this, conditionBuilder);
        this.radioValidator.init(this);
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

    public getChoices(): NodeListOf<HTMLInputElement> {
        return this.choices;
    }

    public getSelectedChoice(): string {
        const selectedChoice = [...this.getChoices()].find(choice => choice.checked);
        return selectedChoice ? selectedChoice.value : '';
    }

    public getField(): HTMLElement {
        return this.field;
    }
}

export default Radio;