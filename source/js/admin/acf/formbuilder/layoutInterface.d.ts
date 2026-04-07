interface BasicLayoutInterface {
    init(): void;
    updateConditionalSelectOptions(optionsNodes: Node): void;
    getId(): string;
    getNameField(): HTMLInputElement;
    getType(): string;
}

type OptionValues = {
    key: string;
    previousKey: string;
    label: string;
}

interface SelectableValuesLayoutInterface extends BasicLayoutInterface {
    getValues(): Array<OptionValues>;
    initOptions(): void;
}

interface BasicLayoutUIInterface {
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

type options = {
    [key: string]: HTMLInputElement;
}