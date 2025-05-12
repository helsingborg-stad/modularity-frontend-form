import Repeater from "./repeater";
import RowBuilder from "./rowBuilder";

class RepeaterFactory {
    public static createRepeater(
        fieldBuilder: FieldBuilderInterface,
        fieldsInitiator: FieldsInitiatorInterface,
        repeaterContainer: HTMLElement,
        addRowButton: HTMLButtonElement,

    ): Repeater|null {
        const template = repeaterContainer.querySelector('[data-js-repeater-row-template]') as HTMLTemplateElement;
        const templateContainer = repeaterContainer.querySelector('[data-js-repeater-row-container]') as HTMLElement;

        if (!template || !templateContainer) {
            console.error("Missing template or template container for repeater");
            return null;
        }

        return new Repeater(
            fieldBuilder,
            fieldsInitiator,
            repeaterContainer,
            addRowButton,
            new RowBuilder(template, templateContainer),
        );
    }
}

export default RepeaterFactory;