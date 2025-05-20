class FieldValidator implements FieldValidatorInterface {
    private parent!: FieldInterface;
    private invalidValidator: ValidationControlInterface|false = false;

    constructor(
        private uiHandler: FieldValidatorUIHandlerInterface, 
        private validators: ValidationControlInterface[] = []
    ) {
    }

    public init(parent: FieldInterface) {
        this.parent = parent;
        this.uiHandler.init(this.parent);
        this.validators.forEach(validator => {
            validator.init(this.parent);
        });
    }

    public validate(): boolean {
        let isValid = true;

        for (const validator of this.validators) {
            const isInvalid = validator.isInvalid();

            if (isInvalid) {
                isValid = false;
                this.uiHandler.addInvalidNotice(isInvalid.getFailedValidationMessage())
                this.invalidValidator = isInvalid;
                break;
            }
        }

        if (isValid) {
            this.uiHandler.removeInvalidNotice();
        }

        if (!isValid && this.invalidValidator) {
            this.uiHandler.updateInvalidNotice(this.invalidValidator.getFailedValidationMessage());
        }

        return isValid;
    }
}

export default FieldValidator