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
            onChange: (html: string) => {},
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

        return new Wysiwyg(
            field as HTMLElement,
            name,
            editor,
            new BasicConditionValidator(),
            new BasicConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                [
                    new HtmlValidator()
                ]
            )
        );
    }
}

export default WysiwygFactory;