class StepUIManager implements StepUIManagerInterface {
    private visibilityHiddenClass: string = 'u-visibility--hidden';
    private isActiveClass: string = 'is-active';
    private validClass: string = 'is-valid';
    private invalidClass: string = 'is-invalid';
    private hasPassedClass: string = 'has-passed';
    private shakeAnimationClass: string = 'animate-shake';
    private exitRightAnimationClass: string = 'animate-exit-right';
    private exitLeftAnimationClass: string = 'animate-exit-left';
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
    }

    /**
     * Handle invalid step
     * 
     * @param step The step to handle
     * @param isInvalid Whether the step is invalid or not
     */
    public handleValidity(step: StepInterface, valid: boolean, allowEdit: boolean = false): void {
        step.getStepContainer().classList.toggle(this.invalidClass, !valid);
        step.getEditItem()?.classList.toggle(this.invalidClass, !valid);
        step.getEditItem()?.classList.toggle(this.validClass, valid);
        valid && allowEdit && step.getEditItem()?.classList.add(this.hasPassedClass);
        this.updateEditItemIcon(step, valid);
    }

    /**
     * Trigger error animation on step
     * 
     * @param step The step to animate
     */
    public triggerErrorAnimation(step: StepInterface): void {
        step.getStepContainer().classList.remove(this.shakeAnimationClass);
        step.getStepContainer().offsetWidth;
        step.getStepContainer().classList.add(this.shakeAnimationClass);
    }

    /**
     * Show and hide steps with animation
     * 
     * @param stepToShow The step to show
     * @param stepToHide The step to hide
     */
   public showAndHideSteps(stepToShow: StepInterface, stepToHide: StepInterface): void {
        const exitClass  = stepToShow.getId() > stepToHide.getId() ? this.exitLeftAnimationClass  : this.exitRightAnimationClass;
        const allAnim = [this.exitLeftAnimationClass, this.exitRightAnimationClass, this.shakeAnimationClass];

        this.hideStep(stepToHide);

        // Clean old classes
        stepToHide.getStepContainer().classList.remove(...allAnim);
        stepToShow.getStepContainer().classList.remove(...allAnim);
        // Trigger reflow to restart animation
        void stepToShow.getStepContainer().offsetWidth;
        // Add new classes
        stepToHide.getStepContainer().classList.add(exitClass);
        stepToShow.getStepContainer().classList.add(this.isActiveClass);
        this.showStep(stepToShow);

        // Cleanup
        const onTransitionEnd = (e: TransitionEvent) => {
            if (e.propertyName !== 'transform' && e.propertyName !== 'opacity') return;
            stepToHide.getStepContainer().classList.remove(this.isActiveClass, exitClass);
            stepToHide.getStepContainer().removeEventListener('transitionend', onTransitionEnd);
        };

        stepToHide.getStepContainer().addEventListener('transitionend', onTransitionEnd);

        // Fallback cleanup
        setTimeout(() => {
            stepToHide.getStepContainer().classList.remove(this.isActiveClass, exitClass);
        }, 400);
    }

    /**
     * Show step
     * 
     * @param step The step to show
     */
    public showStep(step: StepInterface): void {
        step.getStepContainer().classList.add(this.isActiveClass);
        step.getEditItem()?.classList.add(this.isActiveClass);
    }

    /**
     * Hide step
     * 
     * @param step The step to hide
     */
    public hideStep(step: StepInterface): void {
        step.getStepContainer().classList.remove(this.isActiveClass);
        step.getEditItem()?.classList.remove(this.isActiveClass);
    }

    private updateEditItemIcon(step: StepInterface, valid: boolean): void {
        if (!step.getEditItemIcon()) return;

        step.getEditItemIcon()!.setAttribute('data-material-symbol', valid ? 'check' : 'close');
    }
}

export default StepUIManager;