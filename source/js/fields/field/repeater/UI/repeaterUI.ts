import RowBuilder from "./rowBuilder";

class RepeaterUI implements RepeaterUIInterface {
    private rowIndex: number = 0;
    private rowCount: number = 0;
    private repeaterField!: RepeaterInterface;
    private conditionBuilder!: ConditionBuilderInterface;
    private rowFieldsObject: RowFieldsObject = {};
    private rowChangeListeners: RowCountChangeListener[] = []; 

    constructor(
        private fieldBuilder: FieldBuilderInterface,
        private fieldsInitiator: FieldsInitiatorInterface,
        private repeaterContainer: HTMLElement,
        private addRowButton: HTMLButtonElement,
        private rowBuilder: RowBuilder,
        private stepId: string
    ) {}

    public init(repeaterField: RepeaterInterface, conditionBuilder: ConditionBuilderInterface): void {
        this.repeaterField = repeaterField;
        this.conditionBuilder = conditionBuilder;

        if (!this.repeaterField || !this.conditionBuilder) {
            console.error("Repeater field or condition builder is not set");
            return;
        }

        for(let i = 0; i < this.repeaterField.getMinRows(); i++) {
            this.buildRow(false);
        }
        this.setupListener();
    }

    public getRowIndex(): number {
        return this.rowIndex;
    }

    public getRowCount(): number {
        return this.rowCount;
    }

    public addRowChangeListener(rowCountChangeListener: RowCountChangeListener): void {
        this.rowChangeListeners.push(rowCountChangeListener);
    }

    private rowCountChanged(count: number): void {
        this.rowChangeListeners.forEach(listener => listener(count));
    }

    private setupListener() {
        this.addRowButton?.addEventListener('click', (e) => {
            e.preventDefault();
            this.buildRow();
        });
    }

    private removeRow(rowId: string): void {
        for (const fieldName in this.rowFieldsObject[rowId]) {
            this.fieldBuilder.removeField(fieldName);
        }
    }

    private buildRow(includeRemoveRowButton: boolean = true): void {
        const rowId = this.rowIndex.toString();
        const row = this.rowBuilder.createRow(rowId, includeRemoveRowButton);
        const builtRow = this.buildAddedFields(row);
        this.rowFieldsObject[rowId] = builtRow;
        
        row.querySelector('[data-js-repeater-remove-row]')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.rowBuilder.deleteRow(row);
            this.removeRow(rowId);
            this.rowCountChanged(--this.rowCount);
        });

        this.rowCountChanged(++this.rowCount);
        this.rowIndex++;
    }

    private buildAddedFields(row: HTMLElement): FieldsObject {
        const newFieldsObject: FieldsObject = {};

        [...row.querySelectorAll<HTMLElement>('[data-js-field]')].forEach(field => {
            const builtField = this.fieldBuilder.build(field, field.dataset.jsField!, this.stepId);
            newFieldsObject[builtField.getName()] = builtField;
        });

        for (const fieldName in newFieldsObject) {
            newFieldsObject[fieldName].init(this.conditionBuilder);
            newFieldsObject[fieldName].getConditionsHandler().validate();
        }

        this.fieldsInitiator.initializeConditionals(newFieldsObject);
        
        return newFieldsObject;
    }
}

export default RepeaterUI;