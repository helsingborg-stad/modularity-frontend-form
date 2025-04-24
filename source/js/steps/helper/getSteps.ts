import StepFactory from "../stepFactory";

export function getSteps(container: HTMLElement): StepsObject {
    let steps: StepsObject = {};
    container.querySelectorAll('[data-js-frontend-form-step]').forEach((stepContainer) => {
        const id = stepContainer.getAttribute('data-js-frontend-form-step');

        if (!id) {
            console.error("Missing data-js-frontend-form-step attribute");
            return;
        }

        const stepId = parseInt(id);
        steps[stepId] = StepFactory.createStep(stepContainer as HTMLElement);
    });

    return steps;
}