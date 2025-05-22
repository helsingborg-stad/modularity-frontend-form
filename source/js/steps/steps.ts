import StepNavigator from "./stepNavigator";
import StepUIManager from "./StepUIManager";

class Steps implements StepsInterface {
    constructor(
        private steps: StepsObject,
        private validate: ValidateInterface,
        private stepNavigator: StepNavigator,
        private stepUIManager: StepUIManager,
        private nextButton: HTMLButtonElement,
        private previousButton: HTMLButtonElement
    ) {
    }

    public init() {
        this.setupPrevious();
        this.setupNext();
        this.setupEdit();
    }

    private setupEdit() {
        for (const step of Object.values(this.steps)) {
            step.onEdit(() => {
                if (this.stepIsInvalid()) {
                    return;
                }
                const prevStep = this.stepNavigator.getActiveStep();
                const nextStep = this.stepNavigator.goTo(step.getId());

                if (nextStep) {
                    nextStep.showStepAndHidePrevious(prevStep);
                    this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
                }
            })
        }
    }

    private setupNext() {
        this.nextButton.addEventListener('click', async (e) => {
            e.preventDefault();
            if (this.stepIsInvalid()) {
                return;
            }
            const prevStep = this.stepNavigator.getActiveStep();
            const nextStep = this.stepNavigator.goNext();

            if (nextStep) {
                nextStep.showStepAndHidePrevious(prevStep);
                this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
            }
        });
    }

    private setupPrevious() {
        this.previousButton.addEventListener('click', async (e) => {
            e.preventDefault();
            if (this.stepIsInvalid()) {
                return;
            }
            const prevStep = this.stepNavigator.getActiveStep();
            const nextStep = this.stepNavigator.goPrevious();

            if (nextStep) {
                nextStep.showStepAndHidePrevious(prevStep);
                this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
            }
        });
    }

    private stepIsInvalid(): boolean {
        return !this.validate.validateStep(this.stepNavigator.getActiveStepIndex().toString());
    }
}

export default Steps;