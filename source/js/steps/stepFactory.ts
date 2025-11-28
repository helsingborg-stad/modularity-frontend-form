import Step from "./step";

class StepFactory {
	public static createStep(
		stepContainer: HTMLElement,
		step: HTMLElement,
		editItem: HTMLElement | null,
		editItemIcon: HTMLElement | null,
		id: number,
		validator: StepValidatorInterface,
	): Step {
		return new Step(stepContainer, step, editItem, editItemIcon, id, validator);
	}
}

export default StepFactory;
