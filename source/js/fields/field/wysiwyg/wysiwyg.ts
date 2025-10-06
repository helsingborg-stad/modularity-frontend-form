import FieldValidator from "../../validation/fieldValidator";
import Editor from "./editor/editor";

class Wysiwyg implements WysiwygInterface {
    private required: boolean = false;

    constructor(
        private field: HTMLElement,
        private name: string,
        private editor: Editor,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
    }

    public init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getFieldContainer().hasAttribute('data-js-required');
        
    }

    public getName(): string {
        return this.name;
    }

    public getFieldContainer(): HTMLElement {
        return this.field;
    }

    public getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    public getConditionValidator(): ConditionValidatorInterface {
        return this.conditionValidator;
    }

    public getEditor(): Editor {
        return this.editor;
    }

    public getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    public isRequired(): boolean {
        return this.required;
    }

    public hasValue(): boolean {
        return this.editor.hasContent();
    }
}

export default Wysiwyg;