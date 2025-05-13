import Basic from "../basic/basic";
import FileConditionsHandler from "../file/condition/fileConditionsHandler";
import FileConditionValidator from "../file/condition/fileConditionValidator";
import NullFieldFactory from "../nullField/nullFieldFactory";

class ImageFactory {
    static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const input = field.querySelector('input[type="file"]') as HTMLInputElement;

        if (!input) {
            console.error('Image field is not an input element with type "file"');
            return NullFieldFactory.create(field, 'input', name, unstructuredConditions, notices);
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

export default ImageFactory;