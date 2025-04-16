import OpenstreetmapFactory from "./openstreetmap/openstreetmapFactory";
import RepeaterFactory from "./repeater/repeaterFactory";

declare const modularityFrontendForm: ModularityFrontendForm;

class Form {
    constructor(
        private formContainer: HTMLElement,
        private form: HTMLFormElement
    ) {
        this.setupRepeaters()
        this.setupOpenstreetmap();
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