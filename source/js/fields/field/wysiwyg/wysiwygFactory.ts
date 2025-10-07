import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
import BasicConditionsHandler from "../basic/condition/basicConditionHandler";
import BasicConditionValidator from "../basic/condition/basicConditionValidator";
import HtmlValidator from "../basic/validation/htmlValidator";
import EditorConfig from "./editor/editorConfig";
import Wysiwyg from "./wysiwyg";
import Editor from "./editor/editor";
import ContentArea from "./editor/contentArea";
import Actionbar from "./editor/actionbar";
import NullFieldFactory from "../nullField/nullFieldFactory";

class WysiwygFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface,
        stepId: string
    ): FieldInterface {

        const editorConfig = new EditorConfig({
            element: field,
            actions: {
                bold: "bold",
                italic: "italic",
                ulist: "ulist",
                heading2: "heading2",
            }
        });

        const editor = (new Editor(
            editorConfig
        )).init();

        const hiddenField = field.querySelector('[data-js-wysiwyg-hidden-field]') as HTMLInputElement;

        if (!hiddenField) {
            console.error('Failed to find hidden input field needed for Wysiwyg field.')
            return NullFieldFactory.create(field, 'wysiwyg', name, unstructuredConditions, notices, stepId);
        }

        return new Wysiwyg(
            field as HTMLElement,
            hiddenField,
            name,
            editor,
            new BasicConditionValidator(),
            new BasicConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                []
            )
        );
    }
}

export default WysiwygFactory;