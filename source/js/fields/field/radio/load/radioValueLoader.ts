class RadioValueLoader implements RadioValueLoaderInterface {
	private parent!: RadioInterface;
	public init(parent: RadioInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		// TODO: Implement loading logic for radio values
	}
}

export default RadioValueLoader;
