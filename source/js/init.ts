import { getSteps } from "./steps/helper/getSteps";
import StepNavigator from "./steps/stepNavigator";
import Steps from "./steps/steps";
import StepUIManager from "./steps/StepUIManager";
import Submit from "./steps/submit/submit";
import FieldBuilder from "./fields/fieldBuilder";
import ConditionBuilder from "./conditions/conditionBuilder";
import AsyncNonce from "./asyncNonce/asyncNonce";
import SubmitStatusHandler from "./steps/submit/status/handler";
import SubmitStatusRenderer from "./steps/submit/status/render";
import FieldsInitiator from "./fields/fieldsInitiator";
import Notice from "./fields/notice/notice";
import ValidateForm from "./validation/validateForm";

declare const modularityFrontendFormData: ModularityFrontendFormData;
declare const modularityFrontendFormLang: ModularityFrontendFormLang;

class Form {
    constructor(
        private formContainer: HTMLElement,
        private form: HTMLFormElement
    ) {
        new ValidateForm();
        const stepsObject = this.setupSteps();
        this.setupFields(stepsObject);
    }

    private setupFields(stepsObject: StepsObject|null): void {
        if (!stepsObject) {
            console.error("No steps were found");
            return;
        }

        const fieldsInitiatorInstance = new FieldsInitiator();

        const builder = new FieldBuilder(
            fieldsInitiatorInstance,
            new Notice(this.formContainer),
            modularityFrontendFormData,
            modularityFrontendFormLang
        );

        fieldsInitiatorInstance.init(builder);

        for (const stepId in stepsObject) {
            const step = stepsObject[stepId];
            step.getStepContainer().querySelectorAll('[data-js-field]').forEach(element => {
                builder.build(element as HTMLElement, element.getAttribute('data-js-field') ?? '', stepId);
            });
        }

        const conditionBuilder = new ConditionBuilder(builder);

        for (const fieldName in builder.getFieldsObject()) {
            builder.getFieldsObject()[fieldName].init(conditionBuilder);
        }

        fieldsInitiatorInstance.initializeConditionals(builder.getFieldsObject());
    }

    private setupSteps(): StepsObject|null {
        const nextButton = this.formContainer.querySelector('[data-js-frontend-form-next-step]');
        const previousButton = this.formContainer.querySelector('[data-js-frontend-form-previous-step]');

        if (!nextButton || !previousButton) {
            console.error("Missing next or previous button");
            return null;
        }

        const steps = getSteps(this.formContainer);

        new Steps(
            steps,
            new StepNavigator(
                steps,
                new Submit(
                    this.form, 
                    modularityFrontendFormData,
                    modularityFrontendFormLang,
                    new AsyncNonce(modularityFrontendFormData, modularityFrontendFormLang),
                    new SubmitStatusHandler(this.formContainer),
                    new SubmitStatusRenderer(this.formContainer, modularityFrontendFormLang),
                ),
            ),
            new StepUIManager(
                steps,
                modularityFrontendFormLang,
                nextButton as HTMLButtonElement,
                previousButton as HTMLButtonElement
            ),
            nextButton as HTMLButtonElement,
            previousButton as HTMLButtonElement
        ).init();

        return steps;
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