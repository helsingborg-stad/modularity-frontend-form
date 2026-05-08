class HtmlValidator implements ValidationControlInterface {
	private basic!: BasicInterface;

	public init(basic: BasicInterface) {
		this.basic = basic;
	}

	public isInvalid(): false | ValidationControlInterface {
		return this.basic.getField().checkValidity() ? false : this;
	}

	public getFailedValidationMessage(): string {
		return this.basic.getField().validationMessage;
	}
}

export default HtmlValidator;
