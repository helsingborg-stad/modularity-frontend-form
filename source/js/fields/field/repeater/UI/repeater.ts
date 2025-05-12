import RowBuilder from "./rowBuilder";

class Repeater {
    private rowCount: number = 0;

    constructor(
        private fieldBuilder: FieldBuilderInterface,
        private fieldsInitiator: FieldsInitiatorInterface,
        private repeaterContainer: HTMLElement,
        private addRowButton: HTMLButtonElement,
        private rowBuilder: RowBuilder
    ) {}

    public init(): void {
        this.setupListener();
    }

    public getRowCount(): number {
        return this.rowCount;
    }

    private setupListener() {
        this.addRowButton?.addEventListener('click', (e) => {
            e.preventDefault();
            this.rowCount++;
            this.buildAddedFields(this.rowBuilder.createRow(this.rowCount.toString()));
        });
    }

    private buildAddedFields(row: HTMLElement): void {
        const newFieldsObject: FieldsObject = {};

        [...row.querySelectorAll<HTMLElement>('[data-js-field]')].forEach(field => {
            const builtField = this.fieldBuilder.build(field, field.dataset.jsField!);
            newFieldsObject[builtField.getName()] = builtField;
        });

        this.fieldsInitiator.initializeConditionals(newFieldsObject);
        console.log(newFieldsObject);

    }
}

export default Repeater;