class BasicLayoutUI implements BasicLayoutUIInterface {
    private nameFieldInputTimeout: number | undefined;

    constructor(protected layoutData: LayoutData) {
        this.bindNameInputToLayoutUpdate(500);
    }

    public onConditionalSelectChange(callback: () => void): void {
        this.layoutData.conditionalSelect.addEventListener('change', callback);
    }

    public onConditionalOperatorSelectChange(callback: () => void): void {
        this.layoutData.conditionalOperatorSelect.addEventListener('change', callback);
    }

    public onConditionalLogicValueSelectChange(callback: () => void): void {
        this.layoutData.conditionalLogicValueSelect.addEventListener('change', callback);
    }

    public onNameInput(callback: () => void): void {
        this.layoutData.name.addEventListener('input', callback);
    }

    public bindNameInputToLayoutUpdate(debounceMs: number): void {
        this.onNameInput(() => {
            if (this.nameFieldInputTimeout) {
                clearTimeout(this.nameFieldInputTimeout);
            }

            this.nameFieldInputTimeout = window.setTimeout(() => {
                this.dispatchLayoutUpdate();
            }, debounceMs);
        });
    }

    public dispatchLayoutUpdate(): void {
        this.layoutData.layout.dispatchEvent(new CustomEvent('layout:update'));
    }

    public setConditionalLogicValueSelectDisabled(disabled: boolean): void {
        this.layoutData.conditionalLogicValueSelect.disabled = disabled;
    }

    public setConditionalOperatorSelectDisabled(disabled: boolean): void {
        this.layoutData.conditionalOperatorSelect.disabled = disabled;
    }

    public getConditionalSelectValue(): string {
        return this.layoutData.conditionalSelect.value;
    }

    public setConditionalSelectValue(value: string): void {
        this.layoutData.conditionalSelect.value = value;
    }

    public getConditionalOperatorValue(): string {
        return this.layoutData.conditionalOperatorSelect.value;
    }

    public setConditionalOperatorValue(value: string): void {
        this.layoutData.conditionalOperatorSelect.value = value;
    }

    public getConditionalLogicValueSelectValues(): ConditionalLogicValue {
        return Array.from(this.layoutData.conditionalLogicValueSelect.selectedOptions).map(option => option.value);
    }

    public setConditionalLogicValueSelectValues(values: ConditionalLogicValue): void {
        const selectedValues = new Set(values);

        Array.from(this.layoutData.conditionalLogicValueSelect.options).forEach(option => {
            option.selected = selectedValues.has(option.value);
        });
    }

    public clearConditionalLogicValueSelect(): void {
        this.layoutData.conditionalLogicValueSelect.innerHTML = '';
    }

    public setSavedConditionalLogicValue(value: string): void {
        this.layoutData.conditionalLogicSaveField.value = value;
    }

    public getSavedConditionalLogicValue(): string {
        return this.layoutData.conditionalLogicSaveField.value;
    }

    public getNameField(): HTMLInputElement {
        return this.layoutData.name;
    }

    public renderConditionalSelectValuesOptions(optionsNodes: Node, selectedValues: ConditionalLogicValue, values: OptionValues[]): void {
        const valuesByKey = new Set(values.map(value => value.key));
        const remappedValuesByPreviousKey = new Map(values.map(value => [value.previousKey, value.key]));
        const finalSelectedValues = new Set(
            selectedValues
                .map(selectedValue => valuesByKey.has(selectedValue)
                    ? selectedValue
                    : remappedValuesByPreviousKey.get(selectedValue))
                .filter((value): value is string => !!value)
        );

        this.replaceSelectOptions(this.layoutData.conditionalLogicValueSelect, optionsNodes, option => {
            option.selected = finalSelectedValues.has(option.value);
        });
    }

    public renderConditionalOperatorSelectOptions(optionsNodes: Node, selectedValue: string): void {
        this.replaceSelectOptions(this.layoutData.conditionalOperatorSelect, optionsNodes, option => {
            option.selected = option.value === selectedValue;
        });
    }

    public renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void {
        this.replaceSelectOptions(this.layoutData.conditionalSelect, optionsNodes, option => {
            if (option.value === layoutId) {
                return false;
            }

            option.selected = option.value === selectedValue;

            return true;
        });
    }

    private replaceSelectOptions(
        selectElement: HTMLSelectElement,
        optionsNodes: Node,
        mutateOption: (option: HTMLOptionElement) => void | boolean
    ): void {
        selectElement.innerHTML = '';

        while (optionsNodes.firstChild) {
            const option = optionsNodes.firstChild as HTMLOptionElement;
            const shouldAppend = mutateOption(option);

            if (shouldAppend === false) {
                optionsNodes.removeChild(optionsNodes.firstChild);
                continue;
            }

            selectElement.appendChild(option);
        }
    }
}

export default BasicLayoutUI;
