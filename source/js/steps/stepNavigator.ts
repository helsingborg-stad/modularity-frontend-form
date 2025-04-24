class StepNavigator implements StepNavigatorInterface {
    constructor(private steps: StepsObject, private activeStep: number = 0) {
    }

    public goNext(): StepInterface|null {
        if (this.activeStep < Object.keys(this.steps).length - 1) {
            this.activeStep++;
            return this.steps[this.activeStep];
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
}

export default StepNavigator;