import Step from "./step";
import StepFactory from "./stepFactory";

class Steps {
    private steps: { [key: number]: Step } = {};
    private activeStep: number = 0;
    private maxSteps: number = 0;
    private visibilityHiddenClass: string = 'u-visibility--hidden';
    private nextButtonTextElement: HTMLElement | null = null;

    constructor(
        private formContainer: HTMLElement,
        private nextButton: HTMLButtonElement,
        private previousButton: HTMLButtonElement,
        private lang: ModularityFrontendFormLang
    ) {
        this.nextButtonTextElement = this.nextButton.querySelector('.c-button__label-text');
    }

    public init() {
        this.formContainer.querySelectorAll('[data-js-frontend-form-step]').forEach((stepContainer) => {
            const id = stepContainer.getAttribute('data-js-frontend-form-step');

            if (!id) {
                console.error("Missing data-js-frontend-form-step attribute");
                return;
            }

            const stepId = parseInt(id);
            this.steps[stepId] = StepFactory.createStep(this.formContainer, stepContainer as HTMLElement, stepId);
        });

        this.maxSteps = Object.keys(this.steps).length - 1;

        this.setupPrevious();
        this.setupNext();
    }

    private setupNext() {
        this.nextButton.addEventListener('click', async (e) => {
            e.preventDefault();
            if (this.activeStep < this.maxSteps) {
                this.activeStep++;
                this.steps[this.activeStep].showStepAndHidePrevious(this.steps[this.activeStep - 1]);
            }

            if (this.activeStep === this.maxSteps && this.nextButtonTextElement) {
                this.nextButtonTextElement.innerHTML = this.lang.submit ?? 'Submit';
            }
    
            if (this.activeStep > 0) {
                this.previousButton.classList.remove(this.visibilityHiddenClass);
            }
        });
    }

    private setupPrevious() {
        this.previousButton.addEventListener('click', async (e) => {
            e.preventDefault();

            if (this.activeStep > 0) {
                this.activeStep--;
                this.steps[this.activeStep].showStepAndHidePrevious(this.steps[this.activeStep + 1]);
            }

            if (this.activeStep !== this.maxSteps && this.nextButtonTextElement) {
                this.nextButtonTextElement.innerHTML = this.lang.next ?? 'Next';
            }

            if (this.activeStep === 0) {
                this.previousButton.classList.add(this.visibilityHiddenClass);
            }
        });
    }
}

export default Steps;