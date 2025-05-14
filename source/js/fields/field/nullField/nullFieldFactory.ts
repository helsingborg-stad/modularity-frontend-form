import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
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
            new NullFieldConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                [
                ]
            )
        );
    }
}

export default NullFieldFactory;