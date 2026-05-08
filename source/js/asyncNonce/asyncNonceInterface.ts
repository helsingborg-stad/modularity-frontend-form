import StatusHandler from "../formStatus/handler";

interface AsyncNonceInterface {
	setup(form: HTMLFormElement, statusHandler: StatusHandler): Promise<void>;
}

export default AsyncNonceInterface;
