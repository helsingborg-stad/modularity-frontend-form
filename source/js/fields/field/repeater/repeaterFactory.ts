import FieldValidator from "../../validation/fieldValidator";
import FieldValidatorUIHandler from "../../validation/UI/fieldValidatorUIHandler";
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
        notices: NoticeInterface,
        stepId: string
    ): FieldInterface {
        const addRowButton = field.querySelector('[data-js-repeater-add-row]') as HTMLButtonElement;
        if (!addRowButton) {
            console.error('Failed to find add row button for repeater');
            return NullFieldFactory.create(field, 'repeater', name, unstructuredConditions, notices, stepId);
        }

        const repeaterUI = RepeaterUIFactory.createRepeater(fieldBuilder, fieldsInitiator, field, addRowButton, stepId);
        
        if (!repeaterUI) {
            console.error('Failed to create repeater UI instance');
            return NullFieldFactory.create(field, 'repeater', name, unstructuredConditions, notices, stepId);
        }

        // TODO: Need to add validators to check if repeater is empty/required
        return new Repeater(
            field,
            name,
            repeaterUI,
            new RepeaterConditionValidator(),
            new RepeaterConditionsHandler(unstructuredConditions),
            new FieldValidator(
                new FieldValidatorUIHandler(notices),
                [
                ]
            )
        );
    }
}

export default RepeaterFactory;