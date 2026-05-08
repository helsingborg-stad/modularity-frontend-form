import StatusHandler from "../formStatus/handler";
import { SubmitStatus } from "../formStatus/enum";
import AsyncNonceInterface from "./asyncNonceInterface";
class AsyncNonce implements AsyncNonceInterface {
	/**
	 * Constructor for AsyncNonce.
	 * @param modularityFrontendFormData The form data object containing API routes.
	 */
	constructor(
		private modularityFrontendFormData: ModularityFrontendFormData,
		private modularityFrontendFormLang: ModularityFrontendFormLang,
	) {}

	/**
	 * Fetch the nonce from the server.
	 * @returns The nonce string or null if not found.
	 */
	public async get(
		statusHandler: StatusHandler,
		moduleId: number,
	): Promise<string | null> {
		let url = this.modularityFrontendFormData?.apiRoutes?.nonceGet;

		if (!url) {
			statusHandler.setStatus(
				SubmitStatus.Error,
				this.modularityFrontendFormLang?.nonceUrlMissing ??
					"Could not find the nonce URL. Please check your configuration.",
				"link_off",
				0,
			);
			return null;
		}

		url = moduleId
			? (() => {
					const urlBuilder = new URL(url);
					urlBuilder.searchParams.append("module-id", moduleId.toString());
					return urlBuilder.toString();
				})()
			: url;

		try {
			statusHandler.setStatus(
				SubmitStatus.Working,
				this.modularityFrontendFormLang?.nonceRequest ??
					"Fetching security validation key...",
				"lock_open",
				30,
			);

			const response = await fetch(url);
			if (!response.ok) {
				statusHandler.setStatus(
					SubmitStatus.Error,
					this.modularityFrontendFormLang?.nonceRequestFailed ??
						"Failed to fetch security validation key, please try again.",
					"error",
					0,
				);
				throw new Error(`HTTP error: ${response.status}`);
			}

			statusHandler.setStatus(
				SubmitStatus.Working,
				this.modularityFrontendFormLang?.nonceRequestSuccess ??
					"Fetched security validation key...",
				"lock",
				60,
			);

			const json = await response.json();
			return json?.nonce ?? null;
		} catch (error: any) {
			statusHandler.setStatus(
				SubmitStatus.Error,
				this.modularityFrontendFormLang?.nonceRequestFailed ??
					"Failed to fetch security validation key, please try again.",
				"error",
				0,
			);
			return null;
		}
	}

	/**
	 * Inject the nonce into the form as a hidden input field.
	 * @param form The form element to inject the nonce into.
	 * @param nonce The nonce value to inject.
	 */
	public inject(
		form: HTMLFormElement,
		nonce: string,
		nonceElementId: string,
	): void {
		if (this.isNoncePresent(form, nonceElementId)) {
			this.removeNonce(form, nonceElementId);
		}

		form.appendChild(this.createNonceElement(nonce, nonceElementId));
	}

	/**
	 * Check if the nonce is already present in the form.
	 * @param form The form element to check.
	 * @returns True if the nonce is present, false otherwise.
	 */
	public isNoncePresent(
		form: HTMLFormElement,
		nonceElementId: string,
	): boolean {
		return form.querySelector("#" + nonceElementId) !== null;
	}

	/**
	 * Remove the nonce from the form.
	 * @param form The form element to remove the nonce from.
	 */
	public removeNonce(form: HTMLFormElement, nonceElementId: string): void {
		const existingNonceInput = form.querySelector("#" + nonceElementId);
		if (existingNonceInput) {
			form.removeChild(existingNonceInput);
		}
	}

	/**
	 * Create a nonce element to be injected into the form.
	 * @param nonce The nonce value to inject.
	 * @param nonceElementId The ID for the nonce input element.
	 * @returns The created input element.
	 */
	public createNonceElement(
		nonce: string,
		nonceElementId: string,
	): HTMLInputElement {
		const nonceInput = document.createElement("input");

		nonceInput.id = nonceElementId;
		nonceInput.type = "hidden";
		nonceInput.name = "nonce";
		nonceInput.value = nonce;

		nonceInput.setAttribute("aria-hidden", "true");
		nonceInput.setAttribute("autocomplete", "off");
		nonceInput.setAttribute("autocorrect", "off");

		return nonceInput;
	}

	/**
	 * Setup the nonce by fetching it and injecting it into the form.
	 * @param form The form element to inject the nonce into.
	 */
	public async setup(
		form: HTMLFormElement,
		statusHandler: StatusHandler,
	): Promise<void> {
		const moduleId = parseInt(
			form.getAttribute("data-js-frontend-form-id") || "",
		);
		const nonce = await this.get(statusHandler, moduleId);
		if (nonce) {
			this.inject(form, nonce, "async-nonce-element");
		}
	}
}
export default AsyncNonce;
