class FieldValidator implements FieldValidatorInterface {
	private parent!: FieldInterface;
	private serverError: string | null = null;
	private invalidValidator: ValidationControlInterface | false = false;

	constructor(
		private uiHandler: FieldValidatorUIHandlerInterface,
		private validators: ValidationControlInterface[] = [],
	) {}

	public init(parent: FieldInterface) {
		this.parent = parent;
		this.uiHandler.init(this.parent);
		const clearServerError = () => {
			if (this.serverError === null) return;
			this.serverError = null;
			this.uiHandler.removeInvalidNotice();
		};
		this.parent.getFieldContainer().addEventListener("input", clearServerError, true);
		this.parent.getFieldContainer().addEventListener("change", clearServerError, true);
		this.validators.forEach((validator) => {
			validator.init(this.parent);
		});
	}

	public setServerError(message: string): void {
		this.serverError = message;
		this.uiHandler.addInvalidNotice(message);
		this.uiHandler.updateInvalidNotice(message);
	}

	public validate(): boolean {
		if (this.serverError !== null) return false;
		let isValid = true;

		for (const validator of this.validators) {
			const isInvalid = validator.isInvalid();

			if (isInvalid) {
				isValid = false;
				this.uiHandler.addInvalidNotice(isInvalid.getFailedValidationMessage());
				this.invalidValidator = isInvalid;
				break;
			}
		}

		if (isValid) {
			this.uiHandler.removeInvalidNotice();
		}

		if (!isValid && this.invalidValidator) {
			this.uiHandler.updateInvalidNotice(
				this.invalidValidator.getFailedValidationMessage(),
			);
		}

		return isValid;
	}
}

export default FieldValidator;
