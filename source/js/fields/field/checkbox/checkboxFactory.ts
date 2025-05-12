import NullFieldFactory from "../nullField/nullFieldFactory";
import Checkbox from "./checkbox";
import CheckboxConditionsHandler from "./condition/checkboxConditionsHandler";
import CheckboxConditionValidator from "./condition/checkboxConditionValidator";

class CheckboxFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any
    ): FieldInterface {
        const choices = field.querySelectorAll('input[type="checkbox"]') as NodeListOf<HTMLInputElement>;

        if (choices.length === 0) {
            console.error('Checkbox field is missing input elements');
            return NullFieldFactory.create(field, 'checkbox', name, unstructuredConditions);
        }

        return new Checkbox(
            field,
            choices,
            name,
            new CheckboxConditionValidator(),
            new CheckboxConditionsHandler(unstructuredConditions)
        );
    }
}

export default CheckboxFactory;