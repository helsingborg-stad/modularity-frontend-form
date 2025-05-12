import RowBuilder from "./rowBuilder";

class RepeaterUI {
    private rowCount: number = 0;
    private repeaterField!: RepeaterInterface;
    private conditionBuilder!: ConditionBuilderInterface;

    constructor(
        private fieldBuilder: FieldBuilderInterface,
        private fieldsInitiator: FieldsInitiatorInterface,
        private repeaterContainer: HTMLElement,
        private addRowButton: HTMLButtonElement,
        private rowBuilder: RowBuilder
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
            this.buildAddedFields(this.rowBuilder.createRow(this.rowCount.toString()));
            this.rowCount++;
        });
    }

    private buildAddedFields(row: HTMLElement): void {
        const newFieldsObject: FieldsObject = {};

        [...row.querySelectorAll<HTMLElement>('[data-js-field]')].forEach(field => {
            const builtField = this.fieldBuilder.build(field, field.dataset.jsField!);
            newFieldsObject[builtField.getName()] = builtField;
        });

        for (const fieldName in newFieldsObject) {
            newFieldsObject[fieldName].init(this.conditionBuilder);
            newFieldsObject[fieldName].getConditionsHandler().validate();
        }

        this.fieldsInitiator.initializeConditionals(newFieldsObject);
    }
}

export default RepeaterUI;