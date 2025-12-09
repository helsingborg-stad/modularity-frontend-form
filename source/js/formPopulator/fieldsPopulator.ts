import Form from '../form/form';

class FieldsPopulator {
	constructor(
		private form: Form,
		private fieldsObject: FieldsObject,
	) {}
	tryPopulateFields(data: FetchedFormData): void {
		for (const fieldName in data) {
			if (this.fieldsObject.hasOwnProperty(fieldName)) {
				this.fieldsObject[fieldName].getValueLoader().load(data[fieldName]);
			} else {
				console.error(
					`Data could not be loaded for field with name "${fieldName}". It was not found in the fieldsObject`,
				);
			}
		}
	}
}

export default FieldsPopulator;
