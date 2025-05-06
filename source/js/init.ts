import OpenstreetmapFactory from "./openstreetmap/openstreetmapFactory";
import RepeaterFactory from "./repeater/repeaterFactory";
import { getSteps } from "./steps/helper/getSteps";
import StepNavigator from "./steps/stepNavigator";
import Steps from "./steps/steps";
import StepUIManager from "./steps/StepUIManager";
import Submit from "./steps/submit/submit";
import FieldBuilder from "./fields/fieldBuilder";
import Fields from "./fields/fields";
import ConditionBuilder from "./conditions/conditionBuilder";
import AsyncNonce from "./asyncNonce/asyncNonce";
import SubmitStatusHandler from "./steps/submit/status/handler";
import SubmitStatusRenderer from "./steps/submit/status/render";

declare const modularityFrontendFormData: ModularityFrontendFormData;
declare const modularityFrontendFormLang: ModularityFrontendFormLang;

class Form {
    constructor(
        private formContainer: HTMLElement,
        private form: HTMLFormElement
    ) {
        this.setupFields();
        this.setupRepeaters();
        this.setupSteps();
    }

    private setupFields() {
        const builder = new FieldBuilder(modularityFrontendFormData, modularityFrontendFormLang);
        let fieldsObject: FieldsObject = {};

        this.form.querySelectorAll('[data-js-field]').forEach(element => {
            const field = builder.build(element as HTMLElement, element.getAttribute('data-js-field') ?? '');
            fieldsObject[field.getName()] = field;
        });

        const conditionBuilder = new ConditionBuilder(fieldsObject);

        for (const fieldName in fieldsObject) {
            fieldsObject[fieldName].init(conditionBuilder);
        }

        new Fields(this.form, fieldsObject).init();
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
            new StepNavigator(
                steps,
                new Submit(
                    this.form, 
                    modularityFrontendFormData,
                    new AsyncNonce(modularityFrontendFormData),
                    new SubmitStatusHandler(this.formContainer),
                    new SubmitStatusRenderer(this.formContainer)
                ),
            ),
            new StepUIManager(
                steps,
                modularityFrontendFormLang,
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
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-js-frontend-form]').forEach((formContainer) => {
        const form = formContainer.querySelector('form');

        if (form) {
            new Form(formContainer as HTMLElement, form as HTMLFormElement);
        }
    });
});