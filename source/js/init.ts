import StepNavigator from "./steps/stepNavigator";
import Steps from "./steps/steps";
import StepUIManager from "./steps/stepUIManager";
import Submit from "./submit/submit";
import FieldBuilder from "./fields/fieldBuilder";
import ConditionBuilder from "./conditions/conditionBuilder";
import AsyncNonce from "./asyncNonce/asyncNonce";
import StatusHandler from "./formStatus/handler";
import FieldsInitiator from "./fields/fieldsInitiator";
import Notice from "./fields/notice/notice";
import ValidateForm from "./validation/validateForm";
import FormPopulator from "./formPopulator/formPopulator";
import Form from "./form/form";
import FormMode from "./form/formModeEnum";
import StepsFactory from "./steps/stepsFactory";
import StepValidator from "./fields/validation/stepValidator";
import StatusRendererFactory from "./formStatus/statusRendererFactory";
import StatusRendererInterface from "./formStatus/renderInterface";

declare const modularityFrontendFormData: ModularityFrontendFormData;
declare const modularityFrontendFormLang: ModularityFrontendFormLang;

class FormHandler {
    private form: Form;

    constructor(
        private formContainer: HTMLElement,
        private formElement: HTMLFormElement
    ) {
        this.form = new Form(
            this.formElement,
            FormMode.Post
        );
        this.init();
    }

    private init(): void {
        new ValidateForm();
        const statusRenderer = StatusRendererFactory.create(
            this.formContainer,
            modularityFrontendFormLang
        );

        if (!statusRenderer) {
            console.error("StatusRenderer could not be created.");
            return;
        }

        const stepValidator = new StepValidator();
        const stepsObject = this.setupSteps(stepValidator, statusRenderer);

        if (!stepsObject) {
            console.error("No steps were found");
            return;
        }

        const fieldsInitiatorInstance = new FieldsInitiator();
        const builder = this.createBuilder(fieldsInitiatorInstance);
        const conditionBuilder = new ConditionBuilder(builder);
        stepValidator.init(builder);
        fieldsInitiatorInstance.init(builder);
        this.setupFields(stepsObject, builder, conditionBuilder);
        fieldsInitiatorInstance.initializeConditionals(builder.getFieldsObject());
        this.removeLoader(stepsObject);
        this.setupFormPopulator(statusRenderer);
    }

    private setupFormPopulator(statusRenderer: StatusRendererInterface): void {
        const formPopulator = new FormPopulator(
            this.form,
            modularityFrontendFormData,
            modularityFrontendFormLang,
            new AsyncNonce(modularityFrontendFormData, modularityFrontendFormLang),
            new StatusHandler(this.form.formElementContainer),
            statusRenderer
        );
        formPopulator.initialize();
    }

    private createBuilder(fieldsInitiatorInstance: FieldsInitiatorInterface): FieldBuilderInterface {
        const builder = new FieldBuilder(
            fieldsInitiatorInstance,
            new Notice(this.form.formElementContainer),
            modularityFrontendFormData,
            modularityFrontendFormLang
        );

        return builder;
    }

    private setupFields(stepsObject: StepsObject, builder: FieldBuilderInterface, conditionBuilder: ConditionBuilderInterface): void {
        for (const stepId in stepsObject) {
            const step = stepsObject[stepId];
            step.getStepContainer().querySelectorAll('[data-js-field]').forEach(element => {
                builder.build(element as HTMLElement, element.getAttribute('data-js-field') ?? '', stepId);
            });
        }

        for (const fieldName in builder.getFieldsObject()) {
            builder.getFieldsObject()[fieldName].init(conditionBuilder);
        }
    }

    private setupSteps(validator: StepValidatorInterface, statusRenderer: StatusRendererInterface): StepsObject|null {
        const nextButton = this.formContainer.querySelector('[data-js-frontend-form-next-step]');
        const previousButton = this.formContainer.querySelector('[data-js-frontend-form-previous-step]');

        if (!nextButton || !previousButton) {
            console.error("Missing next or previous button");
            return null;
        }

        const steps = StepsFactory.create(this.form.formElementContainer, validator);

        const submit = new Submit(
            this.form, 
            modularityFrontendFormData,
            modularityFrontendFormLang,
            new AsyncNonce(modularityFrontendFormData, modularityFrontendFormLang),
            new StatusHandler(this.formContainer),
            statusRenderer
        );

        new Steps(
            steps,
            new StepNavigator(
                steps,
                validator,
                submit,
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

    private removeLoader(stepsObject: StepsObject): void {
        stepsObject[0].getStepContainer().classList.remove('is-loading');
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('[data-js-frontend-form]').forEach((formContainer) => {
        const form = formContainer.querySelector('form');

        if (form) {
            new FormHandler(formContainer as HTMLElement, form as HTMLFormElement);
        }
    });
});