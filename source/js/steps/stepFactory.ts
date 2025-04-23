import Step from './step';

class StepFactory {
    public static createStep(step: HTMLElement, index: number): Step {
        return new Step(step, index);
    }
}

export default StepFactory;