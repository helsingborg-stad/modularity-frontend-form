interface FieldsInitiatorInterface {
    init(fieldBuilder: FieldBuilderInterface): void;
    initializeConditionals(fields: FieldsObject): void;
}

interface FieldInterface {
    init(conditionBuilder: ConditionBuilderInterface): void;
    getName(): string;
    getField(): HTMLElement;
    getConditionsHandler(): ConditionsHandlerInterface;
    getConditionValidator(): ConditionValidatorInterface;
    getValidator(): FieldValidatorInterface;
    isRequired(): boolean;
    hasValue(): boolean;
}

interface CheckboxInterface extends FieldInterface {
    getChoices(): NodeListOf<HTMLInputElement>;
    getSelectedChoices(): string[];
}

interface RepeaterInterface extends FieldInterface {
    getRowCount(): number;
    getMinRows(): number;
    getMaxRows(): number;
}

interface RadioInterface extends FieldInterface {
    getChoices(): NodeListOf<HTMLInputElement>;
    getSelectedChoice(): string;
}

interface BasicInterface extends FieldInterface {
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
    getFieldsStepObject(): FieldsStepObject;
    removeField(name: string, stepId: string): void;
}

interface FieldValidatorInterface {
    init(field: FieldInterface): void;
    validate(): boolean;
}

interface FieldValidatorUIHandlerInterface {
    init(field: FieldInterface): void;
    addInvalidNotice(message: string): void;
    removeInvalidNotice(): void;
    updateInvalidNotice(message: string): void;
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

type FieldsStepObject = {
    [key: string]: {
        [key: string]: FieldInterface;
    }
}

type ValidationFieldsObject = {
    [key: string]: {
        [key: string]: FieldValidatorInterface;
    }
}