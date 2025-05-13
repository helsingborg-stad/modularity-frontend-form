import NullFieldFactory from "../nullField/nullFieldFactory";
import RadioConditionsHandler from "../radio/condition/radioConditionsHandler";
import RadioConditionValidator from "../radio/condition/radioConditionValidator";
import Radio from "../radio/radio";

class TrueFalseFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const choices = field.querySelectorAll('input[type="radio"]') as NodeListOf<HTMLInputElement>;

        if (choices.length === 0) {
            console.error('Radio field is missing input elements');
            return NullFieldFactory.create(field, 'radio', name, unstructuredConditions, notices);
        }

        return new Radio(
            field,
            choices,
            name,
            new RadioConditionValidator(),
            new RadioConditionsHandler(unstructuredConditions)
        );
    }
}

export default TrueFalseFactory;