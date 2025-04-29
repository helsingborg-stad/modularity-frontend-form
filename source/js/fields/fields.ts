class Fields implements FieldsInterface {
    constructor(private form: HTMLFormElement, private fields: FieldsObject) {
    }

    public init(): void {
        this.setupConditionals();
    }

    private setupConditionals(): void {
        for (const fieldName in this.fields) {
            this.fields[fieldName].getConditionsHandler().getConditions().forEach((condition) => {
                condition.getConditionFieldNames().forEach((conditionFieldName) => {
                    if (this.fields[conditionFieldName]) {
                        this.fields[conditionFieldName].getConditionsHandler().addValueChangeListener(this.fields[fieldName]);
                    }
                });
            });
        }

        for (const fieldName in this.fields) {
            this.fields[fieldName].getConditionsHandler().dispatchUpdateEvent();
        }
    }
}

export default Fields;