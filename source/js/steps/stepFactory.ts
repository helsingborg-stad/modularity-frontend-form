import AnimateStepHelper from './animation/animateStepHelper';
import Step from './step';

class StepFactory {
    public static createStep(stepContainer: HTMLElement, editButton: HTMLElement, step: HTMLElement, id: number): Step {
        return new Step(
            stepContainer,
            editButton,
            step,
            new AnimateStepHelper(step),
            id
        );
    }
}

export default StepFactory;