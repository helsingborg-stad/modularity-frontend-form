import StepFactory from "./stepFactory";

class StepsFactory {
    public static create(container: HTMLElement): StepsObject {
        let steps: StepsObject = {};
        container.querySelectorAll('[data-js-frontend-form-step-container]').forEach((stepContainer) => {
            const step = stepContainer.querySelector('[data-js-frontend-form-step]');
            const id   = step?.getAttribute('data-js-frontend-form-step');
            const editButton = stepContainer.querySelector('[data-js-frontend-form-step-edit]');

            if (!id || !editButton) {
                console.error("Missing data-js-frontend-form-step attribute");
                return;
            }

            const stepId = parseInt(id);
            steps[stepId] = StepFactory.createStep(
                stepContainer as HTMLElement,
                editButton as HTMLElement,
                step as HTMLElement,
                stepId
            );
        });

        return steps;
    }
}

export default StepsFactory;