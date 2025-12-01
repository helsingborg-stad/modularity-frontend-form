import StatusRendererOverlayUI from "./statusRendererOverlayUI";

class StatusRendererButtonUIHandler
	implements StatusRendererButtonUIHandlerInterface
{
	private tryAgainOnclick: FormActionInterface | false = false;
	private returnOnclick: FormActionInterface | false = false;
	constructor(
		private returnButton: HTMLElement,
		private tryAgainButton: HTMLElement,
		private statusRendererOverlayUI: StatusRendererOverlayUI,
	) {
		this.tryAgainListener();
		this.returnToFormListener();
	}

	public toggleReturnButton(
		shouldShow: FormActionInterface | false = false,
	): void {
		this.returnOnclick = shouldShow;

		this.returnButton.classList.toggle("u-display--none", !shouldShow);
	}

	public toggleTryAgainButton(
		shouldShow: FormActionInterface | false = false,
	): void {
		this.tryAgainOnclick = shouldShow;

		this.tryAgainButton.classList.toggle("u-display--none", !shouldShow);
	}

	private tryAgainListener(): void {
		this.tryAgainButton.addEventListener("click", (e) => {
			e.preventDefault();

			if (this.tryAgainOnclick) {
				this.tryAgainOnclick.retry();
			}
		});
	}

	private returnToFormListener(): void {
		this.returnButton.addEventListener("click", () => {
			if (this.returnOnclick) {
				this.returnOnclick.return();
				this.statusRendererOverlayUI.removeOverlay();
				this.statusRendererOverlayUI.removeStatusClasses();
			}
		});
	}
}

export default StatusRendererButtonUIHandler;
