interface FieldsInterface {
    init(): void;
}

interface FieldInterface {
    getName(): string;
    getConditions(): ConditionInterface[];
    addValueChangeListener(field: FieldInterface): void;
    validateConditionals(): boolean;
}

interface FieldBuilderInterface {
    build(field: HTMLElement, type: string): FieldInterface;
    buildCheckbox(field: HTMLElement): FieldInterface;
}

type FieldsObject = {
    [key: string]: FieldInterface;
}