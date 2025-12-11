class SelectValueLoader implements SelectValueLoaderInterface {
	private parent!: SelectInterface;

	public init(parent: SelectInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		if (!value || value === null || value === undefined) {
			return;
		}

		const values = this.convertValueToArrayWithStrings(value);
		const options = this.parent.getOptions();

		options.forEach((option) => {
			if (values.includes(option.value)) {
				option.selected = true;
			} else {
				option.selected = false;
			}
		});
		this.parent.getField().dispatchEvent(new Event('change'));
	}

	private convertValueToArrayWithStrings(value: any): string[] {
		let values = [];

		if (Array.isArray(value)) {
			values = value.map((val) => String(val));
		} else {
			values = [String(value)];
		}

		return values;
	}
}

export default SelectValueLoader;
