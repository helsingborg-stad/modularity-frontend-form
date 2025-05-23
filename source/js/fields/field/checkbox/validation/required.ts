class Required implements ValidationControlInterface {
    private checkbox!: CheckboxInterface;

    constructor(private modularityFrontendFormLang: ModularityFrontendFormLang) {}

    public init(checkbox: CheckboxInterface) {
        this.checkbox = checkbox;
    }

    public isInvalid(): false|ValidationControlInterface {
        if (!this.checkbox.getFieldContainer().dataset.jsRequired) {
            return false;
        }

        return this.checkbox.getSelectedChoices().length > 0 ? false : this;
    }

    public getFailedValidationMessage(): string {
        return this.modularityFrontendFormLang.atLeastOneValueIsRequired ?? 'At least one value is required.';
    }
}

export default Required;