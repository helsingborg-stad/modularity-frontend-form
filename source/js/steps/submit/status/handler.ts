import SubmitStatus from './enum';
import SubmitStatusHandlerInterface from './handlerInterface';

class SubmitStatusHandler implements SubmitStatusHandlerInterface {
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
  public setStatus(status: SubmitStatus, message: string, progress: number): void {
    this.formContainer.dispatchEvent(
      new CustomEvent(
        'submitStatusChanged', {detail: {status, message, progress}}
      )
    );
  }
}
export default SubmitStatusHandler;