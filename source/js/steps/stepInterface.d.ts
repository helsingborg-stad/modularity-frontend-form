interface StepInterface {
    animateStepHelperInstance: AnimateStepHelper
    showStepAndHidePrevious(previousStep: StepInterface): void;
    getId(): number;
    onEdit(callback: () => void): void;
}

interface SubmitInterface {
    submit(): void;
}

interface StepsInterface {
    init(): void;
}

interface StepNavigatorInterface {
    goNext(): StepInterface|null;
    goPrevious(): StepInterface|null;
    getActiveStep(): StepInterface;
    getActiveStepIndex(): number;
}

interface StepUIManager {
    updateButtonStates(activeStep): void;
}

type StepsObject = { [key: number]: StepInterface }