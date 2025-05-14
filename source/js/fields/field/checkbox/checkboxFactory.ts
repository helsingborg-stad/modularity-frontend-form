import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import NullFieldFactory from "../nullField/nullFieldFactory";
import Checkbox from "./checkbox";
import CheckboxConditionsHandler from "./condition/checkboxConditionsHandler";
import CheckboxConditionValidator from "./condition/checkboxConditionValidator";
import Required from "./validation/required";

class CheckboxFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;

        if (choices.length === 0) {
            console.error('Checkbox field is missing input elements');
            return NullFieldFactory.create(field, 'checkbox', name, unstructuredConditions, notices);
        }

        return new Checkbox(
            field,
            choices,
            name,
            new CheckboxConditionValidator(),
            new CheckboxConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                [
                    new Required(),
                ]
            )
        );
    }
}

export default CheckboxFactory;