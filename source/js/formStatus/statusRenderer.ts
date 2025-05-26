import StatusRendererInterface from './renderInterface';
import StatusRendererMessageUI from './statusRendererMessageUI';
import StatusRendererOverlayUI from './statusRendererOverlayUI';
import SubmitStatus from './enum';

class StatusRenderer implements StatusRendererInterface {
    private messageQueue: MessageStatus[] = [];
    private isProcessing: boolean = false;
    private isSubmitting: boolean = false;
    private isLoadingForm: boolean = false;
    private lastMessageStatus: string|null = null;

    constructor(
        private formContainer: HTMLElement,
        private statusRendererMessageUI: StatusRendererMessageUI,
        private statusRendererOverlayUI: StatusRendererOverlayUI
    ) {}

    /**
     * Set up the event listener for submit status changes.
     */
    public setup(): this {
        this.formContainer.addEventListener('submitStatusChanged', (event: Event) => {
            this.handleStatusEvent(event);
        });

        return this;
    }

    // never remove overlay when working is in progress
    // Return to form (if success) or try again button (if error) when submiting

    // When loading/populating form show form directly after loading complete if success, otherwise show error message and try again button


    /**
     * Process the message queue.
     */
    private processQueue(): void {
        if (this.messageQueue.length === 0) {
            this.handleEmptyQueue();
            return;
        }

        this.isProcessing = true;
        this.handleMessage(this.messageQueue.shift()!);
    }

    /**
     * Handle the message from the queue.
     * @param message The message status to process.
     */
    private handleMessage({ status, message, icon, progress, delay }: MessageStatus): void {
        this.statusRendererOverlayUI.applyStatusClass(status);
        this.statusRendererMessageUI.updateProgressBar(progress);
        this.statusRendererMessageUI.updateDescription(message, progress);
        this.statusRendererMessageUI.updateTitle(status);
        this.statusRendererMessageUI.updateIcon(icon);

        setTimeout(() => {
            this.processQueue();
        }, delay);
    }

    /**
     * Handle the case when the queue is empty.
     * Resets the UI after a delay if no messages are being processed.
     */
    private handleEmptyQueue() {
        console.log('No messages in queue, resetting status.');
        this.isProcessing = false;
        setTimeout(() => {
            if (!this.isProcessing) {
                this.resetUI();
            }
        }, 4000);
    }

    /**
     * Resets the UI to its initial state.
     * Removes all status classes and hides the working element.
     */
    private resetUI(): void {
        this.statusRendererOverlayUI.removeStatusClasses();
        this.statusRendererOverlayUI.hideWorkingOverlay();
    }

    /**
     * Handles submit status event: extracts event details, queues, and shows working element if needed.
     */
    private handleStatusEvent(event: Event): void {
        const { status, message, icon, progress, delay = 200 }: MessageStatus = (event as CustomEvent).detail;
        this.messageQueue.push({ status, message, icon, progress, delay });
        if (!this.isProcessing) {
            this.statusRendererOverlayUI.showWorkingOverlay();
            this.processQueue();
        }
    }
}

export default StatusRenderer;