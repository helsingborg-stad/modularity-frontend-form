class BasicLayoutUI implements BasicLayoutUiInterface {
    private nameFieldInputTimeout: number | undefined;

    constructor(private layoutData: LayoutData) {
        this.bindNameInputToLayoutUpdate(500);
    }

    public onConditionalSelectChange(callback: () => void): void {
        this.layoutData.conditionalSelect.addEventListener('change', callback);
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

    public setSavedConditionalLogicValue(value: string): void {
        this.layoutData.conditionalLogicSaveField.value = value;
    }

    public getSavedConditionalLogicValue(): string {
        return this.layoutData.conditionalLogicSaveField.value;
    }

    public getNameField(): HTMLInputElement {
        return this.layoutData.name;
    }

    public renderConditionalSelectValueOptions(optionsNodes: Node, selectedValue: string): void {
        this.layoutData.conditionalLogicValueSelect.innerHTML = '';
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
