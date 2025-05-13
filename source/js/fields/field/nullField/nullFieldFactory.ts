import NullFieldConditionsHandler from "./condition/nullFieldConditionsHandler";
import NullFieldConditionValidator from "./condition/nullFieldConditionValidator";
import NullField from "./nullField";

class NullFieldFactory {
    public static create(
        field: HTMLElement,
        type: string,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {

        return new NullField(
            field,
            type,
            name,
            new NullFieldConditionValidator(),
            new NullFieldConditionsHandler(unstructuredConditions)
        );
    }
}

export default NullFieldFactory;