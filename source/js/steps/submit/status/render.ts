import SubmitStatus from './enum';
import SubmitStatusRendererInterface from './renderInterface';

class SubmitStatusRenderer implements SubmitStatusRendererInterface {
  private messageQueue: Array<{ status: string; message: string; delay: number }> = [];
  private isProcessing: boolean = false;

  constructor(private formContainer: HTMLElement) {}

  /**
   * Set up the event listener for submit status changes.
   */
  public setup(): void {
    this.formContainer.addEventListener('submitStatusChanged', (event: Event) => {
      console.log("SubmitStatusRenderer: submitStatusChanged event triggered");

      const { status, message, delay = 800 } = (event as CustomEvent).detail;

      // Add the new message to the queue
      this.messageQueue.push({ status, message, delay });

      // Start processing the queue if not already processing
      if (!this.isProcessing) {
        this.processQueue();
      }
    });
  }

  /**
   * Process the message queue.
   */
  private processQueue(): void {
    if (this.messageQueue.length === 0) {
      this.isProcessing = false;
      return;
    }

    this.isProcessing = true;

    const { status, message, delay } = this.messageQueue.shift()!;

    // Remove existing status classes
    this.formContainer.classList.remove(
      'is-working',
      'is-success',
      'is-error',
      'is-info',
      'is-warning',
      'is-default'
    );

    // Add the new status class
    this.formContainer.classList.add(`is-${status}`);

    // Render the message into a div.status-message
    let messageEl = this.formContainer.querySelector('.status-message') as HTMLElement;
    if (!messageEl) {
      messageEl = document.createElement('div');
      messageEl.className = 'status-message';
      this.formContainer.appendChild(messageEl);
    }
    messageEl.textContent = message;

    // Wait for the delay before processing the next message
    setTimeout(() => {
      this.processQueue();
    }, delay);
  }
}

export default SubmitStatusRenderer;