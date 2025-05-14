class Required implements ValidationControlInterface {
    private checkbox!: CheckboxInterface;

    public init(checkbox: CheckboxInterface) {
        this.checkbox = checkbox;
    }

    public isInvalid(): false|ValidationControlInterface {
        if (!this.checkbox.getField().dataset.jsFieldRequired) {
            return false;
        }

        return this.checkbox.getSelectedChoices().length > 0 ? false : this;
    }

    // TODO: Translate
    public getFailedValidationMessage(): string {
        return 'A value is required';
    }
}

export default Required;