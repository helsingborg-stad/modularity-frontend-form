import Basic from "../basic/basic";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";

class UrlFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const input = field.querySelector(`input[type="url"]`) as HTMLInputElement;

        if (!input) {
            console.error('Url field is missing input element.');
            return NullFieldFactory.create(field, 'url', name, unstructuredConditions, notices);
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

export default UrlFactory;