import FieldValidator from '../../validation/fieldValidator';
import FieldValidatorUIHandler from '../../validation/UI/fieldValidatorUIHandler';
import Basic from '../basic/basic';
import HtmlValidator from '../basic/validation/htmlValidator';
import FileConditionsHandler from '../file/condition/fileConditionsHandler';
import FileConditionValidator from '../file/condition/fileConditionValidator';
import NullFieldFactory from '../nullField/nullFieldFactory';
import ImageValueLoader from './load/imageValueLoader';

class ImageFactory {
	static create(
		field: HTMLElement,
		name: string,
		unstructuredConditions: any,
		notices: NoticeInterface,
		stepId: string,
	): FieldInterface {
		const input = field.querySelector('input[type="file"]') as HTMLInputElement;

		if (!input) {
			console.error('Image field is not an input element with type "file"');
			return NullFieldFactory.create(field, 'input', name, unstructuredConditions, notices, stepId);
		}

		return new Basic(
			field as HTMLInputElement,
			input,
			name,
			new FileConditionValidator(),
			new FileConditionsHandler(unstructuredConditions),
			new FieldValidator(new FieldValidatorUIHandler(notices), [new HtmlValidator()]),
			new ImageValueLoader(),
		);
	}
}

export default ImageFactory;
