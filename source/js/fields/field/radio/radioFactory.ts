import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import HtmlValidator from "../basic/validation/htmlValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";
import RadioConditionsHandler from "./condition/radioConditionsHandler";
import RadioConditionValidator from "./condition/radioConditionValidator";
import Radio from "./radio";
import Required from "./validation/required";

class RadioFactory {
	public static create(
		field: HTMLElement,
		name: string,
		unstructuredConditions: any,
		modularityFrontendFormLang: ModularityFrontendFormLang,
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
		// TODO: Validator
		return new Radio(
			field,
			choices,
			name,
			new RadioConditionValidator(),
			new RadioConditionsHandler(unstructuredConditions),
			new FieldValidator(new FieldValidatorUIHandler(notices), [
				new Required(modularityFrontendFormLang),
			]),
		);
	}
}

export default RadioFactory;
