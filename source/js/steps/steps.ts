import Step from "./step";
import StepFactory from "./stepFactory";

class Steps {
    private steps: { [key: number]: Step } = {};
    private activeStep: number = 0;
    private maxSteps: number = 0;
    private visibilityHiddenClass: string = 'u-visibility--hidden';
    private displayNoneClass: string = 'u-display--none';
    private displayBlockClass: string = 'u-display--block';

    constructor(
        private formContainer: HTMLElement,
        private nextButton: HTMLButtonElement,
        private previousButton: HTMLButtonElement
    ) {
    }

    public init() {
        this.formContainer.querySelectorAll('[data-js-frontend-form-step]').forEach((stepContainer) => {
            const id = stepContainer.getAttribute('data-js-frontend-form-step');

            if (!id) {
                console.error("Missing data-js-frontend-form-step attribute");
                return;
            }

            const stepId = parseInt(id);
            this.steps[stepId] = StepFactory.createStep(stepContainer as HTMLElement, stepId);
        });

        this.maxSteps = Object.keys(this.steps).length;

        this.setupPrevious();
        this.setupNext();
    }

    private setupNext() {
        this.nextButton.addEventListener('click', (e) => {
            e.preventDefault();
            
            console.log('next')
        });
    }

    private setupPrevious() {
        this.previousButton.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('previous')
        });
    }
}

export default Steps;