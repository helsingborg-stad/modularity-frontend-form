import Step from './step';

class StepFactory {
    public static createStep(
        stepContainer: HTMLElement,
        editButton: HTMLElement,
        step: HTMLElement,
        id: number,
        validator: StepValidatorInterface
    ): Step {
        return new Step(
            stepContainer,
            editButton,
            step,
            id,
            validator
        );
    }
}

export default StepFactory;