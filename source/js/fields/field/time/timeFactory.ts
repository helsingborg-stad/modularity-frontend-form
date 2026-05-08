import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import Basic from "../basic/basic";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import HtmlValidator from "../basic/validation/htmlValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";

class TimeFactory {
	public static create(
		field: HTMLElement,
		name: string,
		unstructuredConditions: any,
		notices: NoticeInterface,
		stepId: string,
	): FieldInterface {
		const input = field.querySelector(`input[type="time"]`) as HTMLInputElement;

		if (!input) {
			console.error("Time field is missing input element.");
			return NullFieldFactory.create(
				field,
				"time",
				name,
				unstructuredConditions,
				notices,
				stepId,
			);
		}

		return new Basic(
			field as HTMLInputElement,
			input,
			name,
			new BasicConditionValidator(),
			new BasicConditionsHandler(unstructuredConditions),
			new FieldValidator(new FieldValidatorUIHandler(notices), [
				new HtmlValidator(),
			]),
		);
	}
}

export default TimeFactory;
