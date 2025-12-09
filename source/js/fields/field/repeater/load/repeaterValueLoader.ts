class RepeaterValueLoader implements RepeaterValueLoaderInterface {
	private parent!: RepeaterInterface;

	public init(parent: RepeaterInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		return;
	}
}

export default RepeaterValueLoader;
