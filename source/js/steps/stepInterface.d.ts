interface StepInterface {
    getId(): number;
    getStepContainer(): HTMLElement;
    getStepContentContainer(): HTMLElement;
    getEditItem(): HTMLElement|null;
    getEditItemIcon(): HTMLElement|null;
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
    handleValidity(step: StepInterface, valid: boolean, allowEdit: boolean = false): void;
    triggerErrorAnimation(step: StepInterface): void;
    showAndHideSteps(stepToShow: StepInterface, stepToHide: StepInterface): void;
    showStep(step: StepInterface): void;
    hideStep(step: StepInterface): void;
}

type StepsObject = { [key: number]: StepInterface }