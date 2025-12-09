class WysiwygValueLoader implements WysiwygValueLoaderInterface {
	private parent!: WysiwygInterface;

	public init(parent: WysiwygInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		this.parent.getEditor().getContentArea().getElement().innerHTML = value ?? '';
	}
}

export default WysiwygValueLoader;
