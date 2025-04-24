import AnimateStepHelper from './animation/animateStepHelper';
import Step from './step';

class StepFactory {
    public static createStep(editButton: HTMLElement, step: HTMLElement, id: number): Step {
        return new Step(
            editButton,
            step,
            new AnimateStepHelper(step),
            id
        );
    }
}

export default StepFactory;