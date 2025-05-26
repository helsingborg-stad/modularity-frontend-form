class StatusRendererOverlayUI {
    private statusClasses: string[] = [
        'is-loading',
        'is-working',
        'is-success',
        'is-error'
    ];
    constructor(
        private formContainer: HTMLElement
    ) {

    }

    /**
     * Adds/removes appropriate status classes.
     */
    public applyStatusClass(status: string): void {
        this.formContainer.classList.remove(...this.statusClasses);
        this.formContainer.classList.add(`is-${status}`);
    }
}

export default StatusRendererOverlayUI;