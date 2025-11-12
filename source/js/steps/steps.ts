import Form from "../form/form";
import StepNavigator from "./stepNavigator";
import StepUIManager from "./stepUIManager";

class Steps implements StepsInterface {
    constructor(
        private form: Form,
        private steps: StepsObject,
        private validator: StepValidatorInterface,
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

    }

    private setupNext() {
        this.nextButton.addEventListener('click', (e) => {
            e.preventDefault();
            const currentStep = this.stepNavigator.getActiveStep();
            const stepIsValid = currentStep.validate();

            this.stepUIManager.handleValidity(currentStep, stepIsValid);
            
            if (!stepIsValid) {
                this.stepUIManager.triggerErrorAnimation(currentStep);
                return;
            }

            const nextStep    = this.stepNavigator.goNext();

            if (nextStep) {
                this.stepUIManager.updateButtonStates(
                    nextStep.getId(),
                    currentStep.getId()
                );

                this.stepUIManager.showAndHideSteps(nextStep, currentStep);
            }

            console.log('Next button clicked');
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
                this.stepUIManager.updateButtonStates(
                    nextStep.getId(),
                    currentStep.getId()
                );

                this.stepUIManager.showAndHideSteps(nextStep, currentStep);
            }
        });
    }
}

export default Steps;