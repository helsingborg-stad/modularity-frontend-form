import StatusRendererInterface from "./renderInterface";
import StatusRendererMessageUI from "./statusRendererMessageUI";
import StatusRendererOverlayUI from "./statusRendererOverlayUI";

class StatusRenderer implements StatusRendererInterface {
	private messageQueue: MessageStatus[] = [];
	private isProcessing: boolean = false;
	private latestStatus: MessageStatus | null = null;

	constructor(
		private formContainer: HTMLElement,
		private statusRendererMessageUI: StatusRendererMessageUI,
		private statusRendererOverlayUI: StatusRendererOverlayUI,
		private statusRendererButtonUIHandler: StatusRendererButtonUIHandlerInterface,
	) {}

	public reset() {
		this.latestStatus = null;
		this.isProcessing = false;
		this.messageQueue = [];
	}

	/**
	 * Set up the event listener for submit status changes.
	 */
	public setup(): this {
		this.formContainer.addEventListener(
			"submitStatusChanged",
			(event: Event) => {
				this.handleStatusEvent(event);
			},
		);

		return this;
	}

	// never remove overlay when working is in progress
	// Return to form (if success) or try again button (if error) when submiting

	// When loading/populating form show form directly after loading complete if success, otherwise show error message and try again button

	/**
	 * Process the message queue.
	 */
	private processQueue(): void {
		if (this.messageQueue[0]) {
			this.latestStatus = this.messageQueue.shift()!;
			this.isProcessing = true;
		}

		this.handleMessage();
	}

	/**
	 * Handle the message from the queue.
	 * @param message The message status to process.
	 */
	private handleMessage(): void {
		this.statusRendererOverlayUI.applyStatusClass(this.latestStatus!.status);

		// check showReturn and showTryAgain
		//add to statusRendererOverlayUI
		this.statusRendererButtonUIHandler.toggleReturnButton(
			this.latestStatus!.showReturn,
		);
		this.statusRendererButtonUIHandler.toggleTryAgainButton(
			this.latestStatus!.showTryAgain,
		);
		this.statusRendererMessageUI.updateProgressBar(this.latestStatus!.progress);
		this.statusRendererMessageUI.updateDescription(
			this.latestStatus!.message,
			this.latestStatus!.progress,
		);
		this.statusRendererMessageUI.updateTitle(this.latestStatus!.status);
		this.statusRendererMessageUI.updateIcon(this.latestStatus!.icon);

		// if hideOverlay is true, remove overlay and reset
		if (this.latestStatus!.hideOverlay) {
			this.statusRendererOverlayUI.removeOverlay(this.latestStatus!.delay);
			this.reset();
			return;
		}

		setTimeout(() => {
			this.processQueue();
		}, this.latestStatus!.delay);
	}

	/**
	 * Handles submit status event: extracts event details, queues, and shows working element if needed.
	 */
	private handleStatusEvent(event: Event): void {
		const {
			status,
			message,
			icon,
			progress,
			delay = 200,
			showReturn = false,
			showTryAgain = false,
			hideOverlay = false,
		}: MessageStatus = (event as CustomEvent).detail;
		this.messageQueue.push({
			status,
			message,
			icon,
			progress,
			delay,
			showReturn,
			showTryAgain,
			hideOverlay,
		});
		if (!this.isProcessing) {
			this.statusRendererOverlayUI.showWorkingOverlay();
			this.processQueue();
		}
	}
}

export default StatusRenderer;
