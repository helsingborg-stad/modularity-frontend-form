class StatusRendererOverlayUI {
	private statusClasses: string[] = [
		"is-loading",
		"is-working",
		"is-success",
		"is-error",
	];
	constructor(
		private formContainer: HTMLElement,
		private workingOverlay: HTMLElement,
	) {}

	public removeOverlay(delay: number = 0): void {
		setTimeout(() => {
			this.workingOverlay.classList.add("u-display--none");
			this.removeStatusClasses();
		}, delay);
	}

	/**
	 * Shows the working element with fade-in animation.
	 */
	public showWorkingOverlay(): void {
		this.workingOverlay.classList.remove("u-display--none");
		this.workingOverlay.style.opacity = "0";
		setTimeout(() => {
			this.workingOverlay.style.opacity = "1";
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
