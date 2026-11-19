import StatusRendererOverlayUI from './statusRendererOverlayUI';

class StatusRendererButtonUIHandler implements StatusRendererButtonUIHandlerInterface {
	private tryAgainOnclick: FormActionInterface | false = false;
	private returnOnclick: FormActionInterface | false = false;
	private returnLabel: string | null;
	constructor(
		private formContainer: HTMLElement,
		private returnButton: HTMLElement,
		private tryAgainButton: HTMLElement,
		private statusRendererOverlayUI: StatusRendererOverlayUI,
	) {
		this.returnLabel = this.returnButton.querySelector('.c-button__label-text')?.textContent ?? null;
		this.tryAgainListener();
		this.returnToFormListener();
	}

	public toggleReturnButton(shouldShow: FormActionInterface | false = false): void {
		this.returnOnclick = shouldShow;
		const label = this.returnButton.querySelector('.c-button__label-text');
		if (label) {
			label.textContent = shouldShow && shouldShow.showFields
				? this.returnButton.dataset.showFieldsLabel ?? this.returnLabel
				: this.returnLabel;
		}

		this.returnButton.classList.toggle('u-display--none', !shouldShow);
	}

	public toggleTryAgainButton(shouldShow: FormActionInterface | false = false): void {
		this.tryAgainOnclick = shouldShow;

		this.tryAgainButton.classList.toggle('u-display--none', !shouldShow);
	}

	private tryAgainListener(): void {
		this.tryAgainButton.addEventListener('click', (e) => {
			e.preventDefault();

			if (this.tryAgainOnclick) {
				this.tryAgainOnclick.retry();
			}
		});
	}

	private returnToFormListener(): void {
		this.returnButton.addEventListener('click', () => {
			if (this.formContainer.dataset.jsFormStatus === 'success') {
				window.location.reload();
			} else if (this.returnOnclick) {
				this.returnOnclick.return();
				this.statusRendererOverlayUI.removeOverlay();
				this.statusRendererOverlayUI.removeStatusClasses();
			}
		});
	}
}

export default StatusRendererButtonUIHandler;
