interface BasicLayoutInterface {
    init(): void;
    updateConditionalSelectOptions(optionsNodes: Node): void;
    getId(): string;
    getNameField(): HTMLInputElement;
    getType(): string;
}

interface SelectableValuesLayoutInterface extends BasicLayoutInterface {
    getValue(): Array<{ value: string; label: string }>;
}

interface BasicLayoutUiInterface {
    onConditionalSelectChange(callback: () => void): void;
    onNameInput(callback: () => void): void;
    bindNameInputToLayoutUpdate(debounceMs: number): void;
    dispatchLayoutUpdate(): void;
    setConditionalLogicValueSelectDisabled(disabled: boolean): void;
    setConditionalOperatorSelectDisabled(disabled: boolean): void;
    getConditionalSelectValue(): string;
    setSavedConditionalLogicValue(value: string): void;
    getSavedConditionalLogicValue(): string;
    getNameField(): HTMLInputElement;
    renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void;
}

type LayoutData = {
    layout: HTMLElement;
    layoutId: string;
    layoutType: string;
    name: HTMLInputElement;
    conditionalSelect: HTMLSelectElement;
    conditionalLogicSaveField: HTMLInputElement;
    conditionalOperatorSelect: HTMLSelectElement;
    conditionalLogicValueSelect: HTMLSelectElement;
    store: FormBuilderStoreInterface;
}