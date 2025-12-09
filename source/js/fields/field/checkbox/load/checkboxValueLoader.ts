class CheckboxValueLoader implements CheckboxValueLoaderInterface {
	private parent!: CheckboxInterface;

	public init(parent: CheckboxInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		// TODO: Implement loading logic for checkbox values
	}
}

export default CheckboxValueLoader;
