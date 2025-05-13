import NullFieldFactory from "../nullField/nullFieldFactory";
import RepeaterConditionsHandler from "./condition/repeaterConditionsHandler";
import RepeaterConditionValidator from "./condition/repeaterConditionValidator";
import Repeater from "./repeater";
import RepeaterUIFactory from "./UI/repeaterUIFactory";

class RepeaterFactory {
    public static create(
        fieldBuilder: FieldBuilderInterface,
        fieldsInitiator: FieldsInitiatorInterface,
        field: HTMLElement,
        name: string,
        unstructuredConditions: any,
        notices: NoticeInterface
    ): FieldInterface {
        const addRowButton = field.querySelector('[data-js-repeater-add-row]') as HTMLButtonElement;
        if (!addRowButton) {
            console.error('Failed to find add row button for repeater');
            return NullFieldFactory.create(field, 'repeater', name, unstructuredConditions, notices);
        }

        const repeaterUI = RepeaterUIFactory.createRepeater(fieldBuilder, fieldsInitiator, field, addRowButton);
        
        if (!repeaterUI) {
            console.error('Failed to create repeater UI instance');
            return NullFieldFactory.create(field, 'repeater', name, unstructuredConditions, notices);
        }

        return new Repeater(
            field,
            name,
            repeaterUI,
            new RepeaterConditionValidator(),
            new RepeaterConditionsHandler(unstructuredConditions)
        );
    }
}

export default RepeaterFactory;