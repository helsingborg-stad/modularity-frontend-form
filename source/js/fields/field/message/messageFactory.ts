import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import NullFieldConditionValidator from "../nullField/condition/nullFieldConditionValidator";
import MessageConditionsHandler from "./condition/messageConditionHandler";
import Message from "./message";

class MessageFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        return new Message(
            field,
            name,
            new NullFieldConditionValidator(),
            new MessageConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                []
            )
        );
    }
}

export default MessageFactory;