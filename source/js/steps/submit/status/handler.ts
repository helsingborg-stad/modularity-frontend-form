import SubmitStatus from './enum';

class SubmitStatusHandler {
  /**
   * Constructor for SubmitStatusHandler.
   * @param formContainer The form container element.
   */
  constructor(
    private formContainer: HTMLElement
  ) {}

  /**
   * Set the status and message.
   * @param status A value from SubmitStatus enum.
   * @param message The status message to display.
   */
  public setStatus(status: SubmitStatus, message: string): void {
    const statusElement = this.formContainer.querySelector('[data-js-frontend-form-status]');
    
    if (!statusElement) {
      console.error("Status element not found.");
      return;
    }

    const statusData = { status, message };
    // Store data directly on the element
    (statusElement as any)._submitStatus = statusData;


    console.log("SubmitStatusHandler: setStatus called with status:", status, "and message:", message);

    // Dispatch a custom event
    const event = new CustomEvent('submitStatusChanged', { detail: statusData });
    statusElement.dispatchEvent(event);
  }
}
export default SubmitStatusHandler;