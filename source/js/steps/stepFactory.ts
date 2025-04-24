import AnimateStepHelper from './animation/animateStepHelper';
import Step from './step';

class StepFactory {
    public static createStep(step: HTMLElement): Step {
        return new Step(
            new AnimateStepHelper(step)
        );
    }
}

export default StepFactory;