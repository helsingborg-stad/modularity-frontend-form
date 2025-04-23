interface AnimateStepHelperInterface {
    preShow(): void;
    show(): void;
    hide(): Promise<void>;
    preHide(): void;
}