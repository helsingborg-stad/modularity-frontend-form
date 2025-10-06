import FieldValidator from "../../validation/fieldValidator";
import Editor from "./editor/editor";

class Wysiwyg implements FieldInterface {
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

    init(conditionBuilder: ConditionBuilderInterface): void {
        this.required = this.getFieldContainer().hasAttribute('data-js-required');
        throw new Error("Method not implemented.");
    }

    getName(): string {
        throw new Error("Method not implemented.");
    }

    getFieldContainer(): HTMLElement {
        return this.field;
    }

    getConditionsHandler(): ConditionsHandlerInterface {
        return this.conditionsHandler;
    }

    getConditionValidator(): ConditionValidatorInterface {
        return this.conditionValidator;
    }

    getValidator(): FieldValidatorInterface {
        return this.validator;
    }

    isRequired(): boolean {
        return this.required;
    }
    
    hasValue(): boolean {
        return this.editor.hasContent();
    }
}

export default Wysiwyg;