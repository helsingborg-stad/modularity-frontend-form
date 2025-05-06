import SubmitStatus from './enum';

interface SubmitStatusHandlerInterface {
  /**
   * Set the status and message.
   * @param status A value from SubmitStatus enum.
   * @param message The status message to display.
   */
  setStatus(status: SubmitStatus, message: string, progress: number): void;
}

export default SubmitStatusHandlerInterface;