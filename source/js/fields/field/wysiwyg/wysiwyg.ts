import FieldValidator from "../../validation/fieldValidator";

class Wysiwyg implements FieldInterface {
    constructor(
        private field: HTMLElement,
        private name: string,
        private conditionValidator: ConditionValidatorInterface,
        private conditionsHandler: ConditionsHandlerInterface,
        private validator: FieldValidatorInterface
    ) {
        console.log(this.field);
    }

    init(conditionBuilder: ConditionBuilderInterface): void {
        throw new Error("Method not implemented.");
    }
    getName(): string {
        throw new Error("Method not implemented.");
    }
    getFieldContainer(): HTMLElement {
        throw new Error("Method not implemented.");
    }
    getConditionsHandler(): ConditionsHandlerInterface {
        throw new Error("Method not implemented.");
    }
    getConditionValidator(): ConditionValidatorInterface {
        throw new Error("Method not implemented.");
    }
    getValidator(): FieldValidatorInterface {
        throw new Error("Method not implemented.");
    }
    isRequired(): boolean {
        throw new Error("Method not implemented.");
    }
    hasValue(): boolean {
        throw new Error("Method not implemented.");
    }
}

export default Wysiwyg;