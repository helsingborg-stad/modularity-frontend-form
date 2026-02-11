import StepNavigator from "./stepNavigator";
import StepUIManager from "./stepUIManager";

class Steps implements StepsInterface {
	private editableSteps: { [key: number]: boolean } = {};

	constructor(
		private steps: StepsObject,
		private stepNavigator: StepNavigator,
		private stepUIManager: StepUIManager,
		private nextButton: HTMLButtonElement,
		private previousButton: HTMLButtonElement,
		private formId: number,
	) {}

	public init() {
		this.editableSteps[this.stepNavigator.getActiveStepIndex()] = true;
		this.setupPrevious();
		this.setupNext();
		this.setupEdit();
		this.updateUrlWithStep(this.stepNavigator.getActiveStepIndex());
	}

	private setupEdit() {
		for (const step of Object.values(this.steps)) {
			if (!step.getEditItem()) continue;
			step.getEditItem()!.addEventListener("click", (e) => {
				e.preventDefault();
				if (step.getId() === this.stepNavigator.getActiveStepIndex()) return;
				if (!this.editableSteps[step.getId()]) return;
				const currentStep = this.stepNavigator.getActiveStep();
				const nextStep = this.stepNavigator.goTo(step.getId());
				this.stepUIManager.handleValidity(currentStep, currentStep.validate());

				if (nextStep) {
					this.handleMove(currentStep, nextStep);
				}
			});
		}
	}

	private setupNext() {
		this.nextButton.addEventListener("click", (e) => {
			e.preventDefault();
			const currentStep = this.stepNavigator.getActiveStep();
			const stepIsValid = currentStep.validate();

			this.stepUIManager.handleValidity(currentStep, stepIsValid, true);

			if (!stepIsValid) {
				this.stepUIManager.triggerErrorAnimation(currentStep);
				return;
			}

			const nextStep = this.stepNavigator.goNext();
			if (nextStep) {
				this.editableSteps[nextStep.getId()] = true;
				this.handleMove(currentStep, nextStep);
			}
		});
	}

	private setupPrevious() {
		this.previousButton.addEventListener("click", (e) => {
			e.preventDefault();

			const currentStep = this.stepNavigator.getActiveStep();
			const previousStep = this.stepNavigator.goPrevious();
			const stepIsValid = currentStep.validate();
			this.stepUIManager.handleValidity(currentStep, stepIsValid);

			if (previousStep) {
				this.handleMove(currentStep, previousStep);
			}
		});
	}

	private handleMove(
		currentStep: StepInterface,
		nextStep: StepInterface,
	): void {
		this.stepUIManager.updateButtonStates(
			nextStep.getId(),
			currentStep.getId(),
		);

		this.stepUIManager.showAndHideSteps(nextStep, currentStep);
		this.updateUrlWithStep(nextStep.getId());
	}

	private updateUrlWithStep(stepIndex: number): void {
		const url = new URL(window.location.href);
		const paramName = `${this.formId}-step`;
		url.searchParams.set(paramName, String(stepIndex + 1));
		window.history.replaceState({}, '', url.toString());
	}
}

export default Steps;
