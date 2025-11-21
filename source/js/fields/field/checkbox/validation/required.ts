class Required implements ValidationControlInterface {
    private checkbox!: CheckboxInterface;

    constructor(private modularityFrontendFormLang: ModularityFrontendFormLang) {}

    public init(checkbox: CheckboxInterface) {
        this.checkbox = checkbox;
    }

    public isInvalid(): false|ValidationControlInterface {
        return this.checkbox.isRequired() && !this.checkbox.hasValue() ? this : false;
    }

    public getFailedValidationMessage(): string {
        return this.modularityFrontendFormLang.atLeastOneValueIsRequired ?? 'At least one value is required.';
    }
}

export default Required;