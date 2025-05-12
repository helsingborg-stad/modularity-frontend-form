import Basic from "../basic/basic";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";

class DateFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any
    ): FieldInterface {
        const input = field.querySelector(`input[type="date"]`) as HTMLInputElement;

        if (!input) {
            console.error('Date field is missing input element.');
            return NullFieldFactory.create(field, 'date', name, unstructuredConditions);
        }

        return new Basic(
            field as HTMLInputElement,
            input,
            name,
            new BasicConditionValidator(),
            new BasicConditionsHandler(unstructuredConditions)
        );
    }
}

export default DateFactory;