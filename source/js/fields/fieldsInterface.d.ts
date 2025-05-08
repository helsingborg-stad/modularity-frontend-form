interface FieldsInterface {
    init(): void;
}

interface FieldInterface {
    init(conditionBuilder: ConditionBuilderInterface): void;
    getName(): string;
    getField(): HTMLElement;
    getConditionsHandler(): ConditionsHandlerInterface;
    getConditionValidator(): ConditionValidatorInterface;
}

interface CheckboxInterface extends FieldInterface {
    getChoices(): NodeListOf<HTMLInputElement>;
    getSelectedChoices(): string[];
}

interface RadioInterface extends FieldInterface {
    getChoices(): NodeListOf<HTMLInputElement>;
    getSelectedChoice(): string;
}

interface BasicInterface extends FieldInterface {
    getInput(): HTMLInputElement;
}

interface FileInterface extends FieldInterface {
    getInput(): HTMLInputElement;
}

interface GoogleMapInterface extends FieldInterface {
    getOpenstreetmap(): OpenstreetmapInterface;
    getHiddenField(): HTMLInputElement;
}

interface SelectInterface extends FieldInterface {
    getSelect(): HTMLSelectElement;
    getOptions(): NodeListOf<HTMLOptionElement>;
    getSelectedOptions(): string[];
}

interface FieldBuilderInterface {
    build(field: HTMLElement, type: string): FieldInterface;
}

interface ConditionsHandlerInterface {
    init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void;
    getConditions(): ConditionInterface[];
    getIsDisabled(): boolean;
    validate(): void;
    addValueChangeListener(field: FieldInterface): void;
    dispatchUpdateEvent(): void;
}

interface ConditionValidatorInterface {
    init(parent: FieldInterface): void;
    validate(condition: Condition): boolean;
}

type FieldsObject = {
    [key: string]: FieldInterface;
}