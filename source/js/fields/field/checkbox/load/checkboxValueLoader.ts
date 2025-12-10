class CheckboxValueLoader implements CheckboxValueLoaderInterface {
	private parent!: CheckboxInterface;

	public init(parent: CheckboxInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		if (!value || value === null || value === undefined) {
			return;
		}

		const values = this.convertValueToArrayWithStrings(value);
		const choices = this.parent.getChoices();

		choices.forEach((choice) => {
			if (values.includes(choice.value)) {
				choice.checked = true;
			} else {
				choice.checked = false;
			}
		});
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

export default CheckboxValueLoader;
