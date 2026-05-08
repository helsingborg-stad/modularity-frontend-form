import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import NullFieldFactory from "../nullField/nullFieldFactory";
import RadioConditionsHandler from "../radio/condition/radioConditionsHandler";
import RadioConditionValidator from "../radio/condition/radioConditionValidator";
import Radio from "../radio/radio";

class TrueFalseFactory {
	public static create(
		field: HTMLElement,
		name: string,
		unstructuredConditions: any,
		notices: NoticeInterface,
		stepId: string,
	): FieldInterface {
		const choices = field.querySelectorAll(
			'input[type="radio"]',
		) as NodeListOf<HTMLInputElement>;

		if (choices.length === 0) {
			console.error("Radio field is missing input elements");
			return NullFieldFactory.create(
				field,
				"radio",
				name,
				unstructuredConditions,
				notices,
				stepId,
			);
		}

		return new Radio(
			field,
			choices,
			name,
			new RadioConditionValidator(),
			new RadioConditionsHandler(unstructuredConditions),
			new FieldValidator(new FieldValidatorUIHandler(notices), []),
		);
	}
}

export default TrueFalseFactory;
