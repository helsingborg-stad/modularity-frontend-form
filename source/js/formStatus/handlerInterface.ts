import SubmitStatus from './enum';

interface StatusHandlerInterface {
  /**
   * Set the status and message.
   * @param status A value from SubmitStatus enum.
   * @param message The status message to display.
   */
  setStatus(status: SubmitStatus, message: string, icon: string, progress: number, delay?: number): void;
}

export default StatusHandlerInterface;