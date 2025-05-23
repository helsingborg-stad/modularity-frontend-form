class StepUIManager implements StepUIManagerInterface {
    private visibilityHiddenClass: string = 'u-visibility--hidden';
    private allowEditClass: string = 'is-editable';
    private invalidClass: string = 'is-invalid';
    private animateErrorClass: string = 'animate-shake';
    private nextButtonLabelElement: HTMLElement|null;
    private maxSteps: number;
    private iconElement: HTMLElement|null;

    constructor(
        private steps: StepsObject,
        private lang: ModularityFrontendFormLang,
        private nextButton: HTMLButtonElement,
        private prevButton: HTMLButtonElement,
    ) {
        this.nextButtonLabelElement = this.nextButton.querySelector('.c-button__label-text');
        this.iconElement = this.nextButton.querySelector('.c-icon');
        this.maxSteps = Object.keys(this.steps).length - 1;
    }

    /**
     * Update the state of the buttons based on the current step
     * 
     * @param activeStep The current active step
     * @param previousActiveStep The previous active step
     */
    public updateButtonStates(activeStep: number, previousActiveStep: number): void {

        const label = activeStep === this.maxSteps ?
        this.lang.submit ?? 'Submit' :
        this.lang.next ?? 'Next';

        if (this.nextButtonLabelElement) {
            this.nextButtonLabelElement.innerHTML = label; 
        }

        if (this.nextButton) {
            this.nextButton.setAttribute('aria-label', label);
        }

        if (this.iconElement) {
            this.iconElement.setAttribute('data-material-symbol', activeStep === this.maxSteps ? 'send' : 'arrow_forward_ios');
        }

        this.prevButton.classList.toggle(this.visibilityHiddenClass, activeStep === 0);
        this.steps[previousActiveStep].getEditButton().classList.remove(this.visibilityHiddenClass);
        this.steps[activeStep].getEditButton().classList.add(this.visibilityHiddenClass);
    }

    /**
     * Handle invalid step
     * 
     * @param step The step to handle
     * @param isInvalid Whether the step is invalid or not
     */
    public handleInvalidStep(step: StepInterface, isInvalid: boolean): void {
        step.getStepContainer().classList.toggle(this.invalidClass, isInvalid);
    }

    /**
     * Check if step is editable
     * 
     * @param step The step to check
     */
    public canEditStep(step: StepInterface): void {
        step.getStepContainer().classList.add(this.allowEditClass);
    }

    /**
     * Adds an error indicator to the step, and animate a shake effect.
     * 
     * @param step The step to add the error indicator to
     */
    public addErrorIndicator(step: StepInterface): void {
        step.getStepContainer().classList.remove(this.animateErrorClass);
        step.getStepContainer().offsetWidth;
        step.getStepContainer().classList.add(this.animateErrorClass);
    }
}

export default StepUIManager;