import AddRow from "./addRow";
import Repeater from "./repeater";
import RowBuilder from "./rowBuilder";

class RepeaterFactory {
    public static createRepeater(repeaterContainer: HTMLElement): Repeater|null {
        const template = repeaterContainer.querySelector('[data-js-repeater-row-template]') as HTMLTemplateElement;
        const templateContainer = repeaterContainer.querySelector('[data-js-repeater-row-container]') as HTMLElement;

        if (!template || !templateContainer) {
            console.error("Missing template or template container for repeater");
            return null;
        }

        return new Repeater(
            repeaterContainer,
            new AddRow(repeaterContainer,  new RowBuilder(template, templateContainer)),
        );
    }
}

export default RepeaterFactory;