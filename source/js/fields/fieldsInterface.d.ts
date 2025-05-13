interface FieldsInitiatorInterface {
    initializeConditionals(fields: FieldsObject): void;
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

interface RepeaterInterface extends FieldInterface {
    getRowCount(): number;
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
    getFieldsObject(): FieldsObject;
}

interface FieldValidatorInterface {
    init(field: FieldInterface): void;
    validate(): boolean;
}

interface FieldValidatorUIHandlerInterface {
    init(field: FieldInterface): void;
}

interface ValidationControlInterface {
    init(field: FieldInterface): void;
    isInvalid(field: FieldInterface): false|string;
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