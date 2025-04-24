interface StepInterface {
    animateStepHelperInstance: AnimateStepHelper
    showStepAndHidePrevious(previousStep: StepInterface): void;
    getId(): number;
    onEdit(callback: () => void): void;
    getStepContainer(): HTMLElement;
    getEditButton(): HTMLElement;
    getEditButtonIcon(): HTMLElement;
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

interface StepUIManagerInterface {
    updateButtonStates(activeStep: number, previousActiveStep: number): void;
}

type StepsObject = { [key: number]: StepInterface }