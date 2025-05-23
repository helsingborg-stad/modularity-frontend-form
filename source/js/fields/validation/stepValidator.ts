class StepValidator implements ValidateInterface {
    private builder!: FieldBuilderInterface;

    public init(builder: FieldBuilderInterface): void {
        this.builder = builder;
    }

    public validateSteps(): boolean {
        const fieldsStepObject = this.builder.getFieldsStepObject();

        const hasInvalidSteps = [];
        for (const stepId in fieldsStepObject) {
            hasInvalidSteps.push(this.validateStep(stepId));
        }

        return !hasInvalidSteps.includes(false);
    }

    public validateStep(stepId: string): boolean {
        const fields = this.builder.getFieldsStepObject()[stepId];
        
        if (!fields) {
            console.error(`No fields found for step ${stepId}`);
            // TODO: What should we do here? Maybe throw an error or return false?
            return true;
        }
        
        const hasInvalidFields = [];
        for (const fieldName in fields) {
            hasInvalidFields.push(fields[fieldName].getValidator().validate());
        }

        return !hasInvalidFields.includes(false);
    }
}

export default StepValidator;