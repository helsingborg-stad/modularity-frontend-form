class StepNavigator implements StepNavigatorInterface {
    constructor(
        private steps: StepsObject,
        private validate: StepValidatorInterface,
        private submit: SubmitInterface,
        private activeStep: number = 0
    ) {
    }

    public goTo(id: number): StepInterface|null {
        if (this.steps[id] && id !== this.activeStep) {
            this.activeStep = id;
            return this.steps[this.activeStep];
        }

        return null;
    }

    public goNext(): StepInterface|null {
        if (this.activeStep < Object.keys(this.steps).length - 1) {
            this.activeStep++;
            return this.steps[this.activeStep];
        }

        if (this.activeStep === Object.keys(this.steps).length - 1) {
            this.handleLastStep();
        }

        return null;
    }

    public goPrevious(): StepInterface|null {
        if (this.activeStep > 0) {
            this.activeStep--;
            return this.steps[this.activeStep];
        }

        return null;
    }

    public getActiveStep(): StepInterface {
        return this.steps[this.activeStep];
    }

    public getActiveStepIndex(): number {
        return this.activeStep;
    }

    private handleLastStep(): void {
        const isValid = this.validate.validateSteps();

        if (!isValid) {
            const invalidStep = this.validate.getInvalidSteps()[0];
            if (invalidStep && this.steps[Number(invalidStep)]) {
                const step = this.steps[Number(invalidStep)];
                step.getEditButton().click();
                step.getStepContainer().scrollIntoView({ behavior: 'smooth' });
            }

            return;
        }

        this.submit.submit();
    }
}

export default StepNavigator;