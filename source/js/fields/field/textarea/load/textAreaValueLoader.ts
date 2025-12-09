class TextAreaValueLoader implements TextAreaValueLoaderInterface {
	private parent!: TextAreaInterface;

	public init(parent: TextAreaInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		this.parent.getField().value = value ?? '';
	}
}

export default TextAreaValueLoader;
