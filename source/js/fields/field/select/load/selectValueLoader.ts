class SelectValueLoader implements SelectValueLoaderInterface {
	private parent!: SelectInterface;

	public init(parent: SelectInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		console.log(value);
		return;
	}
}

export default SelectValueLoader;
