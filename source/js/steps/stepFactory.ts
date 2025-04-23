import AnimateStepHelper from './animation/animateStepHelper';
import Step from './step';

class StepFactory {
    public static createStep(formContainer: HTMLElement, step: HTMLElement, index: number): Step {
        return new Step(
            new AnimateStepHelper(step)
        );
    }
}

export default StepFactory;