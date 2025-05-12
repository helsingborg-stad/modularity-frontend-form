class CheckboxValidator implements FieldValidatorInterface {
    private checkbox!: CheckboxInterface;

    public init(checkbox: CheckboxInterface): void {
        this.checkbox = checkbox;
    }

    public validate() {
        // TODO: add some visible error when failing
        if (!this.checkbox.getField().dataset.jsFieldRequired) {
            return true;
        }

        const isValid = this.checkbox.getSelectedChoices().length > 0;

        this.checkbox.getField().classList.toggle('is-invalid', !isValid);

        return isValid;
    }
}

export default CheckboxValidator