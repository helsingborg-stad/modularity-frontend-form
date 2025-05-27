class StatusRendererOverlayUI {
    private statusClasses: string[] = [
        'is-loading',
        'is-working',
        'is-success',
        'is-error'
    ];
    constructor(
        private formContainer: HTMLElement,
        private workingOverlay: HTMLElement
    ) {

    }

    /**
     * Hides the working element with fade-out animation.
     */
    public hideWorkingOverlay(): void {
        this.workingOverlay.style.opacity = '0';
        setTimeout(() => {
            this.workingOverlay.classList.add('u-display--none');
        }, 1000);
    }

    public showReturnButton(): void {

    }

    public showTryAgainButton(): void {
        
    }

    /**
     * Shows the working element with fade-in animation.
     */
    public showWorkingOverlay(): void {
        this.workingOverlay.classList.remove('u-display--none');
        this.workingOverlay.style.opacity = '0';
        setTimeout(() => {
            this.workingOverlay.style.opacity = '1';
        }, 10);
    }

    /**
     * Adds/removes appropriate status classes.
     */
    public applyStatusClass(status: string): void {
        this.removeStatusClasses();
        this.formContainer.classList.add(`is-${status}`);
    }

    public removeStatusClasses(): void {
        this.formContainer.classList.remove(...this.statusClasses);
    }
}

export default StatusRendererOverlayUI;