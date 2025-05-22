class StepUIManager implements StepUIManagerInterface {
    private visibilityHiddenClass: string = 'u-visibility--hidden';
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

    public updateButtonStates(activeStep: number, previousActiveStep: number): void {
        if (this.nextButtonLabelElement) {
            this.nextButtonLabelElement.innerHTML = activeStep === this.maxSteps ?
                this.lang.submit ?? 'Submit' :
                this.lang.next ?? 'Next'
        }

        if (this.iconElement) {
            this.iconElement.setAttribute('data-material-symbol', activeStep === this.maxSteps ? 'send' : 'arrow_forward_ios');
        }

        this.prevButton.classList.toggle(this.visibilityHiddenClass, activeStep === 0);
        this.steps[previousActiveStep].getEditButton().classList.remove(this.visibilityHiddenClass);
        this.steps[activeStep].getEditButton().classList.add(this.visibilityHiddenClass);
    }
}

export default StepUIManager;