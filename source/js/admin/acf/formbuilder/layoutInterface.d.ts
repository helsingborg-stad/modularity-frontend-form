interface BasicLayoutInterface {
    init(): void;
    updateConditionalSelectOptions(optionsNodes: Node): void;
    updateConditionalSelectValuesOptions(optionsNodes: Node): void;
    getId(): string;
    getNameField(): HTMLInputElement;
    getType(): string;
    getSavedConditionalLogicValue(): string;
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

interface SelectableValuesLayoutUIInterface extends BasicLayoutUIInterface {
    getOptionsFromDOM(): HTMLInputElement[];
    getOptionInputFromElement(element: HTMLElement): HTMLInputElement | null;
    bindOptionInputToKeyUpdate(option: HTMLInputElement, debounceMs: number, getNewKey: (option: HTMLInputElement) => string): void;
    setOptionAttributes(option: HTMLInputElement, key: string, previousKey: string, idCounter: string): void;
    updateOptionKey(option: HTMLInputElement, newKey: string, previousKey: string): void;
    getCurrentOptionKey(option: HTMLInputElement): string;
    getOptionAttributes(option: HTMLInputElement): OptionValues;
    onOptionInput(option: HTMLInputElement, callback: () => void): void;
    isElementWithinLayout(element: HTMLElement): boolean;
}

interface BasicLayoutUIInterface {
    onConditionalSelectChange(callback: () => void): void;
    onNameInput(callback: () => void): void;
    bindNameInputToLayoutUpdate(debounceMs: number): void;
    dispatchLayoutUpdate(): void;
    setConditionalLogicValueSelectDisabled(disabled: boolean): void;
    setConditionalOperatorSelectDisabled(disabled: boolean): void;
    getConditionalSelectValue(): string;
    getConditionalOperatorValue(): string;
    setSavedConditionalLogicValue(value: string): void;
    getSavedConditionalLogicValue(): string;
    getNameField(): HTMLInputElement;
    renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void;
    renderConditionalSelectValuesOptions(optionsNodes: Node, selectedValue: string): void;
    renderConditionalOperatorSelectOptions(optionsNodes: Node, selectedValue: string): void;
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