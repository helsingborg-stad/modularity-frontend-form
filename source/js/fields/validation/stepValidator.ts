class StepValidator implements StepValidatorInterface {
    private builder!: FieldBuilderInterface;
    private invalidSteps: string[] = [];

    public init(builder: FieldBuilderInterface): void {
        this.builder = builder;
    }

    public getInvalidSteps(): string[] {
        return [...this.invalidSteps].sort((a, b) => Number(a) - Number(b));
    }

    public validateSteps(): boolean {
        const fieldsStepObject = this.builder.getFieldsStepObject();

        this.invalidSteps = [];
        for (const stepId in fieldsStepObject) {
            this.validateStep(stepId);
        }

        return this.invalidSteps.length === 0;
    }

    public validateStep(stepId: string): boolean {
        const fields = this.builder.getFieldsStepObject()[stepId];
        
        if (!fields) {
            console.error(`No fields found for step ${stepId}`);
            return true;
        }
        
        const fieldValidity = [];
        for (const fieldName in fields) {
            if (fields[fieldName].getConditionsHandler().getIsDisabled()) {
                continue;
            }

            fieldValidity.push(fields[fieldName].getValidator().validate());
        }

        const valid = !fieldValidity.includes(false);

        if (!valid && !this.invalidSteps.includes(stepId)) {
            this.invalidSteps.push(stepId);
        }

        if (valid && this.invalidSteps.includes(stepId)) {
            this.invalidSteps = this.invalidSteps.filter((id) => id !== stepId);
        }

        return !fieldValidity.includes(false);
    }
}

export default StepValidator;