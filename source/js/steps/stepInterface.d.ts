interface StepInterface {
    getId(): number;
    onEdit(callback: () => void): void;
    getStepContainer(): HTMLElement;
    getStepContentContainer(): HTMLElement;
    getEditButton(): HTMLElement;
    validate(): boolean;
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
    getStep(id: number): StepInterface|null;
    goTo(id: number): StepInterface|null;
}

interface StepUIManagerInterface {
    updateButtonStates(activeStep: number, previousActiveStep: number): void;
    canEditStep(step: StepInterface): void;
    handleValidity(step: StepInterface, valid: boolean): void;
    triggerErrorAnimation(step: StepInterface): void;
    showAndHideSteps(stepToShow: StepInterface, stepToHide: StepInterface): void;
    showStep(step: StepInterface): void;
    hideStep(step: StepInterface): void;
}

type StepsObject = { [key: number]: StepInterface }