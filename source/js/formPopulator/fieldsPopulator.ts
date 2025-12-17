import Form from '../form/form';
import StatusHandlerInterface from '../formStatus/handlerInterface';

class FieldsPopulator {
	constructor(private fieldsObject: FieldsObject) {}

	public tryPopulateFields(data: FetchedFormData): boolean {
		if (!this.checkIfAllFieldsExistInData(data)) {
			return false;
		}

		for (const fieldName in data) {
			if (this.fieldsObject.hasOwnProperty(fieldName)) {
				this.fieldsObject[fieldName].getValueLoader().load(data[fieldName]);
			}
		}

		return true;
	}

	private checkIfAllFieldsExistInData(data: FetchedFormData): boolean {
		for (const fieldName in data) {
			if (!this.fieldsObject.hasOwnProperty(fieldName)) {
				console.error(
					`Data could not be loaded for field with name "${fieldName}". It was not found in the fetched data`,
				);
				return false;
			}
		}

		return true;
	}
}

export default FieldsPopulator;
