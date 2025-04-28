interface FieldsInterface {
    init(): void;
}

interface FieldInterface {
    init(conditionBuilder: ConditionBuilderInterface): void;
    getName(): string;
    getConditionsHandler(): ConditionsHandlerInterface;
}

interface CheckboxInterface extends FieldInterface {
    getChoices(): NodeListOf<HTMLInputElement>;
}

interface FieldBuilderInterface {
    build(field: HTMLElement, type: string): FieldInterface;
    buildCheckbox(field: HTMLElement): FieldInterface;
}

interface ConditionsHandlerInterface {
    init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void;
    getConditions(): ConditionInterface[];
    validate(): boolean;
    addValueChangeListener(field: FieldInterface): void;
}

type FieldsObject = {
    [key: string]: FieldInterface;
}