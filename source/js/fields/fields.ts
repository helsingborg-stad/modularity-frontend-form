class Fields implements FieldsInterface {
    private fields: FieldsObject = {};

    constructor(private form: HTMLFormElement, private fieldsArray: FieldInterface[]) {
    }

    public init(): void {
        this.fieldsArray.forEach((field) => {
            this.fields[field.getName()] = field;
        });

        this.setupConditionals();
    }

    private setupConditionals(): void {
        for (const fieldName in this.fields) {
            this.fields[fieldName].getConditions().forEach((condition) => {
                condition.getConditionFieldNames().forEach((conditionFieldName) => {
                    if (this.fields[conditionFieldName]) {
                        this.fields[conditionFieldName].addValueChangeListener(this.fields[fieldName]);
                    }
                });
            });
        }
    }
}

export default Fields;