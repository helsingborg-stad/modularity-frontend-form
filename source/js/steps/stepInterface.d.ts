interface StepInterface {
    animateStepHelperInstance: AnimateStepHelper
    showStepAndHidePrevious(previousStep: StepInterface): void;
    getId(): number;
    onEdit(callback: () => void): void;
    getStepContainer(): HTMLElement;
    getstepContentContainer(): HTMLElement;
    getEditButton(): HTMLElement;
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
    canEditStep(step: StepInterface): void;
    handleInvalidStep(step: StepInterface, isInvalid: boolean): void;
    addActiveClass(step: StepInterface): void;
    removeActiveClass(step: StepInterface): void;
}

type StepsObject = { [key: number]: StepInterface }