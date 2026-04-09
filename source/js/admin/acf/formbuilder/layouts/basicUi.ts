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
            clearTimeout(this.nameFieldInputTimeout);
            this.nameFieldInputTimeout = window.setTimeout(() => this.dispatchLayoutUpdate(), debounceMs);
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
        const resolvedKeys = new Set(
            selectedValues
                .map(selected => values.find(v => v.key === selected)?.key ?? values.find(v => v.previousKey === selected)?.key)
                .filter((v): v is string => v !== undefined)
        );

        this.renderToSelect(
            this.layoutData.conditionalLogicValueSelect,
            optionsNodes,
            option => resolvedKeys.has(option.value)
        );
    }

    public renderConditionalOperatorSelectOptions(optionsNodes: Node, selectedValue: string): void {
        this.renderToSelect(
            this.layoutData.conditionalOperatorSelect,
            optionsNodes,
            option => option.value === selectedValue
        );
    }

    public renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void {
        this.renderToSelect(
            this.layoutData.conditionalSelect,
            optionsNodes,
            option => option.value === selectedValue,
            option => option.value === layoutId
        );
    }

    private renderToSelect(
        select: HTMLSelectElement,
        optionsNodes: Node,
        isSelected?: (option: HTMLOptionElement) => boolean,
        shouldSkip?: (option: HTMLOptionElement) => boolean
    ): void {
        select.innerHTML = '';

        while (optionsNodes.firstChild) {
            const option = optionsNodes.firstChild as HTMLOptionElement;

            if (shouldSkip?.(option)) {
                optionsNodes.removeChild(option);
                continue;
            }

            if (isSelected?.(option)) {
                option.selected = true;
            }

            select.appendChild(option);
        }
    }
}

export default BasicLayoutUI;
