class Step implements StepInterface {
    constructor(
        private stepContainer: HTMLElement,
        private editButton: HTMLElement,
        private stepContentContainer: HTMLElement,
        private id: number,
        private validator: StepValidatorInterface
    ) {
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

    public validate(): boolean {
        return this.validator.validateStep(this.getId().toString());
    }

    public getStepContentContainer(): HTMLElement {
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