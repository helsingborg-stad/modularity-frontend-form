import StepNavigator from "./stepNavigator";
import StepUIManager from "./stepUIManager";

class Steps implements StepsInterface {
    private editableSteps: {[key: number]: boolean} = {};

    constructor(
        private steps: StepsObject,
        private stepNavigator: StepNavigator,
        private stepUIManager: StepUIManager,
        private nextButton: HTMLButtonElement,
        private previousButton: HTMLButtonElement
    ) {
    }

    public init() {
        this.editableSteps[this.stepNavigator.getActiveStepIndex()] = true;
        this.setupPrevious();
        this.setupNext();
        this.setupEdit();
    }

    private setupEdit() {
        for (const step of Object.values(this.steps)) {
            if (!step.getEditItem()) continue;
            step.getEditItem()!.addEventListener('click', (e) => {
                e.preventDefault();
                if (step.getId() === this.stepNavigator.getActiveStepIndex()) return;
                if (!this.editableSteps[step.getId()]) return;
                const currentStep = this.stepNavigator.getActiveStep();
                const nextStep = this.stepNavigator.goTo(step.getId());
                this.stepUIManager.handleValidity(currentStep, currentStep.validate());

                if (nextStep) {
                    this.handleMove(currentStep, nextStep);
                }
            });
        }
    }

    private setupNext() {
        this.nextButton.addEventListener('click', (e) => {
            e.preventDefault();
            const currentStep = this.stepNavigator.getActiveStep();
            const stepIsValid = currentStep.validate();

            this.stepUIManager.handleValidity(currentStep, stepIsValid, true);
            
            if (!stepIsValid) {
                this.stepUIManager.triggerErrorAnimation(currentStep);
                return;
            }

            const nextStep    = this.stepNavigator.goNext();
            if (nextStep) {
                this.editableSteps[nextStep.getId()] = true;
                this.handleMove(currentStep, nextStep);
            }
        });
    }

    private setupPrevious() {
        this.previousButton.addEventListener('click', (e) => {
            e.preventDefault();

            const currentStep = this.stepNavigator.getActiveStep();
            const nextStep = this.stepNavigator.goPrevious();
            const stepIsValid = currentStep.validate();
            this.stepUIManager.handleValidity(currentStep, stepIsValid);

            if (nextStep) {
                this.handleMove(currentStep, nextStep);
            }
        });
    }

    private handleMove(currentStep: StepInterface, nextStep: StepInterface): void {
        this.stepUIManager.updateButtonStates(
            nextStep.getId(),
            currentStep.getId()
        );

        this.stepUIManager.showAndHideSteps(nextStep, currentStep);
    }
}

export default Steps;