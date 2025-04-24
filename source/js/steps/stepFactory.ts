import AnimateStepHelper from './animation/animateStepHelper';
import Step from './step';

class StepFactory {
    public static createStep(editButton: HTMLElement, editButtonIcon: HTMLElement, step: HTMLElement, id: number): Step {
        return new Step(
            editButton,
            editButtonIcon,
            step,
            new AnimateStepHelper(step),
            id
        );
    }
}

export default StepFactory;