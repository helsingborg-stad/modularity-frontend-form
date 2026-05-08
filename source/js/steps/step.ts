class Step implements StepInterface {
	constructor(
		private stepContainer: HTMLElement,
		private stepContentContainer: HTMLElement,
		private editItem: HTMLElement | null,
		private editItemIcon: HTMLElement | null,
		private id: number,
		private validator: StepValidatorInterface,
	) {}

	public getStepContainer(): HTMLElement {
		return this.stepContainer;
	}

	public getEditItem(): HTMLElement | null {
		return this.editItem;
	}

	public getEditItemIcon(): HTMLElement | null {
		return this.editItemIcon;
	}

	public validate(): boolean {
		return this.validator.validateStep(this.getId().toString());
	}

	public getStepContentContainer(): HTMLElement {
		return this.stepContentContainer;
	}

	public getId(): number {
		return this.id;
	}
}

export default Step;
