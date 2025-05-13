import Basic from "../basic/basic";
import NullFieldFactory from "../nullField/nullFieldFactory";
import FileConditionsHandler from "./condition/fileConditionsHandler";
import FileConditionValidator from "./condition/fileConditionValidator";

class FileFactory {
    static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const input = field.querySelector('input[type="file"]') as HTMLInputElement;

        if (!input) {
            console.error('Input field is not an input element with type "file"');
            return NullFieldFactory.create(field, 'file', name, unstructuredConditions, notices);
        }

        return new Basic(
            field as HTMLInputElement,
            input,
            name,
            new FileConditionValidator(),
            new FileConditionsHandler(unstructuredConditions)
        );
    }
}

export default FileFactory;