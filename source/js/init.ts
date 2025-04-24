import OpenstreetmapFactory from "./openstreetmap/openstreetmapFactory";
import RepeaterFactory from "./repeater/repeaterFactory";
import { getSteps } from "./steps/helper/getSteps";
import StepNavigator from "./steps/stepNavigator";
import Steps from "./steps/steps";
import StepUIManager from "./steps/StepUIManager";


declare const modularityFrontendForm: ModularityFrontendForm;

class Form {
    constructor(
        private formContainer: HTMLElement,
        private form: HTMLFormElement
    ) {
        this.setupSteps();
        this.setupRepeaters()
        this.setupOpenstreetmap();
    }

    private setupSteps() {
        const nextButton = this.formContainer.querySelector('[data-js-frontend-form-next-step]');
        const previousButton = this.formContainer.querySelector('[data-js-frontend-form-previous-step]');

        if (!nextButton || !previousButton) {
            console.error("Missing next or previous button");
            return;
        }

        const steps = getSteps(this.formContainer);

        new Steps(
            steps,
            new StepNavigator(steps),
            new StepUIManager(steps,
                modularityFrontendForm.lang,
                nextButton as HTMLButtonElement,
                previousButton as HTMLButtonElement
            ),
            nextButton as HTMLButtonElement,
            previousButton as HTMLButtonElement).init();
    }

    private setupRepeaters() {
        this.formContainer.querySelectorAll('[data-js-form-repeater]').forEach((repeaterContainer) => {
            RepeaterFactory.createRepeater(repeaterContainer as HTMLElement)?.init();
        });
    }

    private setupOpenstreetmap() {
        if (!modularityFrontendForm.placeSearchApiUrl) {
            return;
        }

        this.formContainer.querySelectorAll('[data-js-openstreetmap]').forEach((osmContainer) => {
            OpenstreetmapFactory.createOpenstreetmap(osmContainer as HTMLElement, modularityFrontendForm)?.init();
        });
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-js-frontend-form]').forEach((formContainer) => {
        const form = formContainer.querySelector('form');

        if (form) {
            new Form(formContainer as HTMLElement, form as HTMLFormElement);
        }
    });
});