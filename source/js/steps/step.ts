class Step implements StepInterface {

    constructor(
        private editButton: HTMLElement,
        private stepContainer: HTMLElement,
        public animateStepHelperInstance: AnimateStepHelperInterface,
        private id: number
    ) {
    }

    public async showStepAndHidePrevious(previousStep: StepInterface): Promise<void> {
        previousStep.animateStepHelperInstance.preHide();
        this.animateStepHelperInstance.preShow();
        await previousStep.animateStepHelperInstance.hide();
        this.animateStepHelperInstance.show();
    }

    public onEdit(callback: () => void): void {
        this.editButton.addEventListener('click', (e: Event) => {
            e.preventDefault();
            callback();
        });
    }

    public getId(): number {
        return this.id;
    }
}

export default Step;