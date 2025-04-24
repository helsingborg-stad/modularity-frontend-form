class Step implements StepInterface {

    constructor(public animateStepHelperInstance: AnimateStepHelperInterface) {
    }

    public async showStepAndHidePrevious(previousStep: StepInterface): Promise<void> {
        previousStep.animateStepHelperInstance.preHide();
        this.animateStepHelperInstance.preShow();
        await previousStep.animateStepHelperInstance.hide();
        this.animateStepHelperInstance.show();
    }
}

export default Step;