import StepFactory from "./stepFactory";

class StepsFactory {
	public static create(
		container: HTMLElement,
		validator: StepValidatorInterface,
	): StepsObject {
		let steps: StepsObject = {};
		container
			.querySelectorAll("[data-js-frontend-form-step-container]")
			.forEach((stepContainer) => {
				const step = stepContainer.querySelector(
					"[data-js-frontend-form-step]",
				);
				const id = step?.getAttribute("data-js-frontend-form-step");
				const editItem = container.querySelector(
					`[data-js-frontend-form-progressbar-step="${id}"]`,
				);
				const editItemIcon = editItem?.querySelector(`[data-material-symbol]`);

				if (!id) {
					console.error("Missing data-js-frontend-form-step attribute");
					return;
				}

				const stepId = parseInt(id);
				steps[stepId] = StepFactory.createStep(
					stepContainer as HTMLElement,
					step as HTMLElement,
					editItem as HTMLElement,
					editItemIcon as HTMLElement,
					stepId,
					validator,
				);
			});

		return steps;
	}
}

export default StepsFactory;
