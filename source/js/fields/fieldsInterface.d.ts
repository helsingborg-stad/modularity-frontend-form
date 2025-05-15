interface FieldsInitiatorInterface {
    initializeConditionals(fields: FieldsObject): void;
}

interface FieldInterface {
    init(conditionBuilder: ConditionBuilderInterface): void;
    getName(): string;
    getField(): HTMLElement;
    getConditionsHandler(): ConditionsHandlerInterface;
    getConditionValidator(): ConditionValidatorInterface;
    getValidator(): FieldValidatorInterface;
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
    build(field: HTMLElement, type: string, step: string): FieldInterface;
    getFieldsObject(): FieldsObject;
    removeField(name: string): void;
}

interface FieldValidatorInterface {
    init(field: FieldInterface): void;
    validate(): boolean;
    valueChangeListener(): void;
}

interface FieldValidatorUIHandlerInterface {
    init(field: FieldInterface): void;
    addInvalidNotice(message: string): void;
    removeInvalidNotice(): void;
}

interface ValidationControlInterface {
    init(field: FieldInterface): void;
    isInvalid(): false|ValidationControlInterface;
    getFailedValidationMessage(): string;
}

interface ConditionsHandlerInterface {
    init(parent: FieldInterface, conditionsBuilder: ConditionBuilderInterface): void;
    getConditions(): ConditionInterface[];
    getIsDisabled(): boolean;
    validate(): void;
    addValueChangeListener(field: FieldInterface): void;
    checkConditions(): void;
}

interface ConditionValidatorInterface {
    init(parent: FieldInterface): void;
    validate(condition: Condition): boolean;
}

type FieldsObject = {
    [key: string]: FieldInterface;
}

type ValidationFieldsObject = {
    [key: string]: {
        [key: string]: FieldValidatorInterface;
    }
}