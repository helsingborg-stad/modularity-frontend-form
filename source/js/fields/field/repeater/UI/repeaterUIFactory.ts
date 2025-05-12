import RepeaterUI from "./repeaterUI";
import RowBuilder from "./rowBuilder";

class RepeaterUIFactory {
    public static createRepeater(
        fieldBuilder: FieldBuilderInterface,
        fieldsInitiator: FieldsInitiatorInterface,
        repeaterContainer: HTMLElement,
        addRowButton: HTMLButtonElement,

    ): RepeaterUIInterface|null {
        const template = repeaterContainer.querySelector('[data-js-repeater-row-template]') as HTMLTemplateElement;
        const templateContainer = repeaterContainer.querySelector('[data-js-repeater-row-container]') as HTMLElement;

        if (!template || !templateContainer) {
            console.error("Missing template or template container for repeater");
            return null;
        }

        return new RepeaterUI(
            fieldBuilder,
            fieldsInitiator,
            repeaterContainer,
            addRowButton,
            new RowBuilder(template, templateContainer),
        );
    }
}

export default RepeaterUIFactory;