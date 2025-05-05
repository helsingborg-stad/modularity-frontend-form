import SubmitStatusHandler from '../steps/submit/status/handler';

interface AsyncNonceInterface {
  setup(form: HTMLFormElement, submitStatusHandler: SubmitStatusHandler): Promise<void>;
}

export default AsyncNonceInterface;