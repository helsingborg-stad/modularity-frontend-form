import NullFieldConditionValidator from "../nullField/condition/nullFieldConditionValidator";
import MessageConditionsHandler from "./condition/messageConditionHandler";
import Message from "./message";

class MessageFactory {
    public static create(field: HTMLElement, name: string, unstructuredConditions: any): FieldInterface {
        return new Message(
            field,
            name,
            new NullFieldConditionValidator(),
            new MessageConditionsHandler(unstructuredConditions)
        );
    }
}

export default MessageFactory;