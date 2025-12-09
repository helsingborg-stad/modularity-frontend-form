class BasicValueLoader implements BasicValueLoaderInterface {
	private parent!: BasicInterface;
	public init(parent: BasicInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		this.parent.getField().value = value;
	}
}

export default BasicValueLoader;
