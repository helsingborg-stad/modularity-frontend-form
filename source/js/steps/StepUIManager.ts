class StepUIManager implements StepUIManagerInterface {
    private visibilityHiddenClass: string = 'u-visibility--hidden';
    private editIconName: string = 'edit';
    private closeIconName: string = 'close';
    private iconAttribute: string = 'data-material-symbol';
    private nextButtonLabelElement: HTMLElement|null;
    private maxSteps: number;

    constructor(
        private steps: StepsObject,
        private lang: ModularityFrontendFormLang,
        private nextButton: HTMLButtonElement,
        private prevButton: HTMLButtonElement,
    ) {
        this.nextButtonLabelElement = this.nextButton.querySelector('.c-button__label-text');
        this.maxSteps = Object.keys(this.steps).length - 1;
    }

    public updateButtonStates(activeStep: number, previousActiveStep: number): void {
        if (this.nextButtonLabelElement) {
            this.nextButtonLabelElement.innerHTML = activeStep === this.maxSteps ?
                this.lang.submit ?? 'Submit' :
                this.lang.next ?? 'Next'
        }

        this.prevButton.classList.toggle(this.visibilityHiddenClass, activeStep === 0);
        this.steps[activeStep].getEditButtonIcon().setAttribute(this.iconAttribute, this.closeIconName);
        this.steps[previousActiveStep].getEditButtonIcon().setAttribute(this.iconAttribute, this.editIconName);
    }
}

export default StepUIManager;