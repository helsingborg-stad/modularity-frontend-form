class Step implements StepInterface {

    constructor(
        private stepContainer: HTMLElement,
        private editButton: HTMLElement,
        private stepContentContainer: HTMLElement,
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

    public getstepContentContainer(): HTMLElement {
        return this.stepContentContainer;
    }

    public getEditButton(): HTMLElement {
        return this.editButton;
    }

    public getId(): number {
        return this.id;
    }
}

export default Step;