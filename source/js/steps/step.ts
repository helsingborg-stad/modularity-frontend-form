class Step implements StepInterface {

    constructor(
        private editButton: HTMLElement,
        private stepContainer: HTMLElement,
        public animateStepHelperInstance: AnimateStepHelperInterface,
        private id: number
    ) {
    }

    public async showStepAndHidePrevious(previousStep: StepInterface): Promise<void> {
        await previousStep.animateStepHelperInstance.hide();
        this.animateStepHelperInstance.show();
    }

    public onEdit(callback: () => void): void {
        this.editButton.addEventListener('click', (e: Event) => {
            e.preventDefault();
            callback();
        });
    }

    public getStepContainer(): HTMLElement {
        return this.stepContainer;
    }

    public getEditButton(): HTMLElement {
        return this.editButton;
    }

    public getId(): number {
        return this.id;
    }
}

export default Step;