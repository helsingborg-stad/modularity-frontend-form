class StepValidator implements ValidateInterface {
    private builder!: FieldBuilderInterface;
    private invalidSteps: string[] = [];

    public init(builder: FieldBuilderInterface): void {
        this.builder = builder;
    }

    public getInvalidSteps(): string[] {
        return this.invalidSteps;
    }

    public validateSteps(): boolean {
        const fieldsStepObject = this.builder.getFieldsStepObject();

        this.invalidSteps = [];
        for (const stepId in fieldsStepObject) {
            const isValid = this.validateStep(stepId);
            if (!isValid) {
                this.invalidSteps.push(stepId);
            }
        }

        return this.invalidSteps.length === 0;
    }

    public validateStep(stepId: string): boolean {
        const fields = this.builder.getFieldsStepObject()[stepId];
        
        if (!fields) {
            console.error(`No fields found for step ${stepId}`);
            return true;
        }
        
        const hasInvalidFields = [];
        for (const fieldName in fields) {
            hasInvalidFields.push(fields[fieldName].getValidator().validate());
        }

        const valid = !hasInvalidFields.includes(false);

        if (!valid && !this.invalidSteps.includes(stepId)) {
            this.invalidSteps.push(stepId);
        }

        if (valid && this.invalidSteps.includes(stepId)) {
            this.invalidSteps = this.invalidSteps.filter((id) => id !== stepId);
        }

        return !hasInvalidFields.includes(false);
    }
}

export default StepValidator;