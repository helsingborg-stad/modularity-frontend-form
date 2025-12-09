class SelectValueLoader implements SelectValueLoaderInterface {
	private parent!: SelectInterface;

	public init(parent: SelectInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		// TODO: Implement select value loading logic
		return;
	}
}

export default SelectValueLoader;
