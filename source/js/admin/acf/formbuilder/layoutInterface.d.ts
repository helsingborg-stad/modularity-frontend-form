interface BasicLayoutInterface {
    init(): void;
    updateConditionalSelectOptions(optionsNodes: Node): void;
    updateConditionalSelectValuesOptions(optionsNodes: Node, values: OptionValues[]): void;
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

type ConditionalLogicValue = string[];

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
    getOptionIdCounter(option: HTMLInputElement): string | null;
    getOptionAttributes(option: HTMLInputElement): OptionValues;
    onOptionInput(option: HTMLInputElement, callback: () => void): void;
    isElementWithinLayout(element: HTMLElement): boolean;
    dispatchSelectableUpdate(): void;
}

interface BasicLayoutUIInterface {
    onConditionalSelectChange(callback: () => void): void;
    onConditionalOperatorSelectChange(callback: () => void): void;
    onConditionalLogicValueSelectChange(callback: () => void): void;
    onNameInput(callback: () => void): void;
    bindNameInputToLayoutUpdate(debounceMs: number): void;
    dispatchLayoutUpdate(): void;
    setConditionalLogicValueSelectDisabled(disabled: boolean): void;
    setConditionalOperatorSelectDisabled(disabled: boolean): void;
    getConditionalSelectValue(): string;
    setConditionalSelectValue(value: string): void;
    getConditionalOperatorValue(): string;
    setConditionalOperatorValue(value: string): void;
    getConditionalLogicValueSelectValues(): ConditionalLogicValue;
    setConditionalLogicValueSelectValues(values: ConditionalLogicValue): void;
    setSavedConditionalLogicValue(value: string): void;
    getSavedConditionalLogicValue(): string;
    getNameField(): HTMLInputElement;
    renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void;
    renderConditionalSelectValuesOptions(optionsNodes: Node, selectedValues: ConditionalLogicValue, values: OptionValues[]): void;
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