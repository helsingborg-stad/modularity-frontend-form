import RowBuilder from "./rowBuilder";

class AddRow {
    private button: HTMLButtonElement;
    private rowCount: number = 0;

    constructor(
        private repeaterContainer: HTMLElement,
        private rowBuilder: RowBuilder
    ) {
        this.button = this.repeaterContainer.querySelector('[data-js-repeater-add-row]') as HTMLButtonElement;
    }

    public setupListener() {
        this.button?.addEventListener('click', (e) => {
            e.preventDefault();
            this.rowCount++;
            this.rowBuilder.createRow(this.rowCount.toString());
        });
    }
}

export default AddRow;