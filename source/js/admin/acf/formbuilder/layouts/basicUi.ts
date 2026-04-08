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

    public getConditionalLogicValueSelectValue(): string {
        return this.layoutData.conditionalLogicValueSelect.value;
    }

    public setConditionalLogicValueSelectValue(value: string): void {
        this.layoutData.conditionalLogicValueSelect.value = value;
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

    public renderConditionalSelectValuesOptions(optionsNodes: Node, selectedValue: string, values: OptionValues[]): void {
        const remappedSelectedValue = values.find(value => value.previousKey === selectedValue)?.key;
        const finalSelectedValue = values.some(value => value.key === selectedValue)
            ? selectedValue
            : remappedSelectedValue;

        this.layoutData.conditionalLogicValueSelect.innerHTML = '';

        while (optionsNodes.firstChild) {
            const option = optionsNodes.firstChild as HTMLOptionElement;

            if (option.value === finalSelectedValue) {
                option.selected = true;
            }

            this.layoutData.conditionalLogicValueSelect.appendChild(option);
        }
    }

    public renderConditionalOperatorSelectOptions(optionsNodes: Node, selectedValue: string): void {
        this.layoutData.conditionalOperatorSelect.innerHTML = '';

        while (optionsNodes.firstChild) {
            const option = optionsNodes.firstChild as HTMLOptionElement;

            if (option.value === selectedValue) {
                option.selected = true;
            }

            this.layoutData.conditionalOperatorSelect.appendChild(option);
        }
    }

    public renderConditionalSelectOptions(optionsNodes: Node, layoutId: string, selectedValue: string): void {
        this.layoutData.conditionalSelect.innerHTML = '';

        while (optionsNodes.firstChild) {
            const option = optionsNodes.firstChild as HTMLOptionElement;

            if (option.value === layoutId) {
                optionsNodes.removeChild(optionsNodes.firstChild);
                continue;
            }

            if (option.value === selectedValue) {
                option.selected = true;
            }

            this.layoutData.conditionalSelect.appendChild(option);
        }
    }
}

export default BasicLayoutUI;
