import RowBuilder from "./rowBuilder";

class RepeaterUI implements RepeaterUIInterface {
    private rowIndex: number = 0;
    private rowCount: number = 0;
    private repeaterField!: RepeaterInterface;
    private conditionBuilder!: ConditionBuilderInterface;
    private rowFieldsObject: RowFieldsObject = {};
    private rowChangeListeners: RowCountChangeListener[] = []; 
    private rowCountElement: HTMLElement|null = null;

    constructor(
        private fieldBuilder: FieldBuilderInterface,
        private fieldsInitiator: FieldsInitiatorInterface,
        private repeaterContainer: HTMLElement,
        private addRowButton: HTMLButtonElement,
        private rowBuilder: RowBuilder,
        private stepId: string
    ) {}

    public init(
        repeaterField: RepeaterInterface,
        conditionBuilder: ConditionBuilderInterface,
    ): void {
        this.repeaterField = repeaterField;
        this.conditionBuilder = conditionBuilder;
        this.rowCountElement = this.repeaterField.getFieldContainer().querySelector('[data-js-repeater-row-counter]');

        if (!this.repeaterField || !this.conditionBuilder) {
            console.error("Repeater field or condition builder is not set");
            return;
        }

        this.setupListeners();

        for(let i = 0; i < this.repeaterField.getMinRows(); i++) {
            this.buildRow(false);
        }
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

    private setupListeners() {
        this.addRowButton.addEventListener('click', (e) => {
            e.preventDefault();
            this.buildRow();
        });

        if (this.rowCountElement) {
            this.addRowChangeListener(count => {
                this.rowCountElement!.innerHTML = count.toString();
            });
        }

        this.addRowChangeListener(count => {
            this.addRowButton.disabled = count >= this.repeaterField.getMaxRows()
        });
    }

    private removeRow(rowId: string): void {
        for (const fieldName in this.rowFieldsObject[rowId]) {
            this.fieldBuilder.removeField(fieldName, this.stepId);
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

        // Only focus if the row is not auto generated
        if (includeRemoveRowButton) {
            this.focusOnNewRow(row);
        }

        this.rowCountChanged(++this.rowCount);
        this.rowIndex++;
    }

    // Focus on the first focusable element in the new row
    private focusOnNewRow(row: HTMLElement): void {
        const firstFocusable = row.querySelector<HTMLElement>(':not(.u-display--none) input, :not(.u-display--none) select, :not(.u-display--none) textarea, :not(.u-display--none) fieldset');

        if (!firstFocusable) {
            return;
        }

        firstFocusable.focus();
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