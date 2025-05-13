import Basic from "../basic/basic";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";

class TimeFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const input = field.querySelector(`input[type="time"]`) as HTMLInputElement;

        if (!input) {
            console.error('Time field is missing input element.');
            return NullFieldFactory.create(field, 'time', name, unstructuredConditions, notices);
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

export default TimeFactory;