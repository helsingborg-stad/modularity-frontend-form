interface SubmitInterface extends FormActionInterface {
	submit(event: Event): void | Promise<void>;
}
