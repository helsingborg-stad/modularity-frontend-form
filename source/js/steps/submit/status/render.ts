import SubmitStatus from './enum';

class SubmitStatusRenderer {
  private container: HTMLElement;

  constructor(container: HTMLElement) {
    this.container = container;
  }

  /**
   * Set up the event listener for submit status changes.
   */
  public setup(): void {
    this.container.addEventListener('submitStatusChanged', (event: Event) => {

      console.log("SubmitStatusRenderer: submitStatusChanged event triggered");
      
      const { status, message } = (event as CustomEvent).detail;

      // Remove existing status classes
      this.container.classList.remove(
        'is-working',
        'is-success',
        'is-error',
        'is-info',
        'is-warning',
        'is-default'
      );

      // Add the new status class
      this.container.classList.add(`is-${status}`);

      // Render the message into a div.status-message
      let messageEl = this.container.querySelector('.status-message') as HTMLElement;
      if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.className = 'status-message';
        this.container.appendChild(messageEl);
      }
      messageEl.textContent = message;
    });
  }
}

export default SubmitStatusRenderer;