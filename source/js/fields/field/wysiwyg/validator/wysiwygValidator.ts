class WysiwygValidator implements ValidationControlInterface {
	private wysiwyg!: WysiwygInterface;

	constructor(private modularityFrontendFormLang: ModularityFrontendFormLang) {}

	public init(parent: WysiwygInterface): void {
		this.wysiwyg = parent;
	}

	public isInvalid(): false | ValidationControlInterface {
		return this.wysiwyg.isRequired() && !this.wysiwyg.hasValue() ? this : false;
	}

	public getFailedValidationMessage(): string {
		return (
			this.modularityFrontendFormLang.errorWysiwyg ?? "Please add some content"
		);
	}
}

export default WysiwygValidator;
