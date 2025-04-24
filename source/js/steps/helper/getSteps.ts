import StepFactory from "../stepFactory";

export function getSteps(container: HTMLElement): StepsObject {
    let steps: StepsObject = {};
    container.querySelectorAll('[data-js-frontend-form-step-container]').forEach((stepContainer) => {
        const step = stepContainer.querySelector('[data-js-frontend-form-step]');
        const id   = step?.getAttribute('data-js-frontend-form-step');
        const editButton = stepContainer.querySelector('[data-js-frontend-form-step-edit]');
        const editButtonIcon = editButton?.querySelector('.c-icon');

        if (!id || !editButton || !editButtonIcon) {
            console.error("Missing data-js-frontend-form-step attribute");
            return;
        }

        const stepId = parseInt(id);
        steps[stepId] = StepFactory.createStep(
            editButton as HTMLElement,
            editButtonIcon as HTMLElement,
            step as HTMLElement,
            stepId
        );
    });

    return steps;
}