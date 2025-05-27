import {SubmitStatus} from './enum';
import StatusHandlerInterface from './handlerInterface';

class StatusHandler implements StatusHandlerInterface {
  /**
   * Constructor for StatusHandler.
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
  public setStatus(
    status: SubmitStatus,
    message: string,
    icon: string,
    progress: number,
    delay: number = 200,
    showReturn: boolean = false,
    showTryAgain: boolean = false
    
  ): void {
    this.formContainer.dispatchEvent(
      new CustomEvent(
        'submitStatusChanged', {detail: {status, message, icon, progress, delay, showReturn, showTryAgain} as MessageStatus}
      )
    );
  }
}
export default StatusHandler;