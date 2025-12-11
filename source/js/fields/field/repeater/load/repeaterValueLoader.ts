class RepeaterValueLoader implements RepeaterValueLoaderInterface {
	private parent!: RepeaterInterface;

	constructor(private repeaterUI: RepeaterUIInterface) {}

	public init(parent: RepeaterInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		if (!Array.isArray(value) || value.length === 0) {
			console.error('Invalid value for repeater field. Expected a non-empty array.');
			return;
		}

		console.log(this.repeaterUI.getRowCount());
		const rowCount = this.repeaterUI.getRowCount();
		if (rowCount === 0) {
		}

		value.forEach((row) => {
			this.repeaterUI.buildRow(true, false);
		});

		console.log(value);
		console.log(this.repeaterUI.getRows());
		console.log(this.parent.getName());
		return;
	}
}

export default RepeaterValueLoader;
