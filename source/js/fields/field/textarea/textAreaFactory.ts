import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import HtmlValidator from "../basic/validation/htmlValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";
import TextArea from "./textArea";

class TextAreaFactory {
    static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface,
        stepId: string
    ): FieldInterface {
        const textarea = field.querySelector(`textarea`) as HTMLTextAreaElement;

        if (!textarea) {
            console.error('TextArea field is missing textarea element');
            return NullFieldFactory.create(field, 'textarea', name, unstructuredConditions, notices, stepId);
        }

        return new TextArea(
            field as HTMLInputElement,
            textarea,
            name,
            new BasicConditionValidator(),
            new BasicConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                [
                    new HtmlValidator(),
                ]
            )
        );
    }
}

export default TextAreaFactory;