class Required implements ValidationControlInterface {
	private radio!: RadioInterface;

	constructor(private modularityFrontendFormLang: ModularityFrontendFormLang) {}

	public init(radio: RadioInterface) {
		this.radio = radio;
	}

	public isInvalid(): false | ValidationControlInterface {
		return this.radio.isRequired() && !this.radio.hasValue() ? this : false;
	}

	public getFailedValidationMessage(): string {
		return (
			this.modularityFrontendFormLang.errorRequired ?? "Please select a choice."
		);
	}
}

export default Required;
