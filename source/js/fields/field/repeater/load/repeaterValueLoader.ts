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

		const linesToCreate = value.length - this.repeaterUI.getRowCount();

		for (let i = 0; i < linesToCreate; i++) {
			this.repeaterUI.buildRow(true, false);
		}

		const rowFieldsObject = this.repeaterUI.getRows();

		for (const [index, rowValue] of value.entries()) {
			if (typeof rowValue !== 'object' || rowValue === null) {
				console.error(`Invalid row value at index ${index}. Expected an object.`);
				continue;
			}

			this.addDataToRow(index, rowValue, rowFieldsObject);
		}
	}

	private addDataToRow(rowIndex: number, rowValue: { [key: string]: any }, rowFieldsObject: RowFieldsObject): void {
		for (const [fieldName, fieldValue] of Object.entries(rowValue)) {
			const keyToLookForInRowFieldsObject = `[${this.parent.getName()}][${rowIndex}][${fieldName}]`;

			if (!rowFieldsObject[rowIndex] || !rowFieldsObject[rowIndex][keyToLookForInRowFieldsObject]) {
				console.error(
					`Field with name ${keyToLookForInRowFieldsObject} not found in row ${rowIndex}. Skipping this field.`,
				);
				continue;
			}

			const field = rowFieldsObject[rowIndex][keyToLookForInRowFieldsObject];
			field.getValueLoader().load(fieldValue);
		}
	}
}

export default RepeaterValueLoader;
