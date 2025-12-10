class RadioValueLoader implements RadioValueLoaderInterface {
	private parent!: RadioInterface;
	public init(parent: RadioInterface): void {
		this.parent = parent;
	}

	public load(value: any): void {
		const sanitizedValue = this.extractValue(value);

		if (sanitizedValue === null) {
			return;
		}

		const choices = this.parent.getChoices();
		const defaultSelected = this.parent.getSelectedChoice();

		choices.forEach((choice) => {
			if (choice.value === sanitizedValue) {
				choice.checked = true;
			} else {
				choice.checked = false;
			}
		});
	}

	private extractValue(value: any): string | null {
		if (value === null || value === undefined) {
			return null;
		}

		if (typeof value === 'boolean') {
			return String(+value);
		}

		return String(value);
	}
}

export default RadioValueLoader;
