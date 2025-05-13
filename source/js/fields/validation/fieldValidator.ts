class FieldValidator implements FieldValidatorInterface {
    private parent!: FieldInterface;
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
                this.uiHandler.handleInvalid(isInvalid)
                break;
            }
        }

        return isValid;
    }
}

export default FieldValidator