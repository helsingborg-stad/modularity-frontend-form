import RowBuilder from "./rowBuilder";

class RepeaterUI {
    private rowCount: number = 0;
    private repeaterField!: RepeaterInterface;
    private conditionBuilder!: ConditionBuilderInterface;
    private rowFieldsObject: RowFieldsObject = {};

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

        this.setupListener();
    }

    public getRowCount(): number {
        return this.rowCount;
    }

    private setupListener() {
        this.addRowButton?.addEventListener('click', (e) => {
            e.preventDefault();
            const rowId = this.rowCount.toString();
            const row = this.rowBuilder.createRow(rowId);
            const builtRow = this.buildAddedFields(row);
            this.rowFieldsObject[rowId] = builtRow;
            
            row.querySelector('[data-js-repeater-remove-row]')?.addEventListener('click', (e) => {
                e.preventDefault();
                this.rowBuilder.deleteRow(row);
                this.removeRow(rowId);
            });

            this.rowCount++;
        });
    }

    private removeRow(rowId: string): void {
        for (const fieldName in this.rowFieldsObject[rowId]) {
            this.fieldBuilder.removeField(fieldName);
        }
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