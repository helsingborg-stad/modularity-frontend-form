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

        return isValid;
    }

    // TODO: Is this needed?
    // How should this work? Do we only listen after we run validate (trying to send the form)?
    // Or should we always use validate? since it will run only after having a value and 
    public valueChangeListener(): void {
        if (this.invalidValidator) {
            this.invalidValidator = this.invalidValidator.isInvalid();
        }

        if (!this.invalidValidator) {
            this.uiHandler.removeInvalidNotice();
        }
    }
}

export default FieldValidator