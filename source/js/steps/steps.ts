import StepNavigator from "./stepNavigator";
import StepUIManager from "./stepUIManager";

class Steps implements StepsInterface {
    constructor(
        private steps: StepsObject,
        private validate: StepValidatorInterface,
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
                this.handleInvalidStep(this.stepIsInvalid());
 
                const prevStep = this.stepNavigator.getActiveStep();
                const nextStep = this.stepNavigator.goTo(step.getId());

                if (nextStep) {
                    nextStep.showStepAndHidePrevious(prevStep);
                    this.stepUIManager.addActiveClass(nextStep);
                    this.stepUIManager.removeActiveClass(prevStep);
                    this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
                }
            })
        }
    }

    private setupNext() {
        this.nextButton.addEventListener('click', async (e) => {
            e.preventDefault();
            const isInvalid = this.stepIsInvalid();
            this.handleInvalidStep(isInvalid);

            if (isInvalid) {
                this.stepUIManager.addErrorIndicator(this.stepNavigator.getActiveStep());
                return;
            }

            const prevStep = this.stepNavigator.getActiveStep();
            const nextStep = this.stepNavigator.goNext();

            if (nextStep) {
                nextStep.showStepAndHidePrevious(prevStep);
                this.stepUIManager.addActiveClass(nextStep);
                this.stepUIManager.removeActiveClass(prevStep);
                this.stepUIManager.canEditStep(nextStep);
                this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
            }
        });
    }

    private setupPrevious() {
        this.previousButton.addEventListener('click', async (e) => {
            e.preventDefault();
            this.handleInvalidStep(this.stepIsInvalid());
            const prevStep = this.stepNavigator.getActiveStep();
            const nextStep = this.stepNavigator.goPrevious();

            if (nextStep) {
                this.stepUIManager.removeActiveClass(prevStep);
                this.stepUIManager.addActiveClass(nextStep);
                nextStep.showStepAndHidePrevious(prevStep);
                this.stepUIManager.updateButtonStates(this.stepNavigator.getActiveStepIndex(), prevStep.getId());
            }
        });
    }

    private handleInvalidStep(isInvalid: boolean): void {
        this.stepUIManager.handleInvalidStep(this.stepNavigator.getActiveStep(), isInvalid);
    }

    private stepIsInvalid(): boolean {
        return !this.validate.validateStep(this.stepNavigator.getActiveStepIndex().toString());
    }
}

export default Steps;