import AsyncNonce from '../asyncNonce/asyncNonce';
import StatusHandler from '../formStatus/handler';
import { SubmitStatus } from '../formStatus/enum';
import FormMode from '../form/formModeEnum';
import Form from '../form/form';
import StatusRendererInterface from '../formStatus/renderInterface';

class FormDataFetcher implements FormActionInterface {
	private formDataListeners: Array<(data: FetchedFormData) => void> = [];
	constructor(
		private form: Form,
		private formParams: FormParams,
		private modularityFrontendFormData: ModularityFrontendFormData,
		private modularityFrontendFormLang: ModularityFrontendFormLang,
		private asyncNonce: AsyncNonce,
		private statusHandler: StatusHandler,
		private statusRenderer: StatusRendererInterface,
	) {}

	public retry(): void {
		this.statusRenderer.reset();
		this.tryFetchFormData();
	}

	public return(): void {
		this.statusRenderer.reset();
	}

	public subscribeToFetchedFormData(callback: (data: FetchedFormData) => void): void {
		this.formDataListeners.push(callback);
	}

	/**
	 * Initializes the form populator by fetching and populating the form data.
	 */
	public async tryFetchFormData(): Promise<void> {
		// Set the initial status to working
		this.statusHandler.setStatus(SubmitStatus.Loading, 'Loading form data...', 'file_open', 0, 1000);

		const formData = await this.get(this.formParams.postId, this.formParams.token);
		if (formData) {
			this.form.mode = FormMode.Update; // Set the form mode to update

			//Show success message
			this.statusHandler.setStatus(
				SubmitStatus.Success,
				this.modularityFrontendFormLang?.loadingSuccessful ?? 'Form loaded successfully!',
				'celebration',
				100,
				2000,
				false,
				false,
				true,
			);

			// this.populateForm(formData);
			this.formDataListeners.forEach((listener) => {
				listener(formData);
			});
		}
	}

	/**
	 * Fetches the form data from the server.
	 * @param postId The ID of the post.
	 * @param token The security validation key.
	 * @returns The form data as a string or null if not found.
	 */
	public async get(postId: number, token: Token32): Promise<FetchedFormData | null> {
		let url = this.modularityFrontendFormData?.apiRoutes?.getForm;

		if (!url) {
			this.statusHandler.setStatus(SubmitStatus.Loading, 'Could not find url.', 'file_open', 100, 2000, this, this);
			return null;
		}

		const nonce = await this.asyncNonce.get(this.statusHandler, this.form.formId);

		url = (() => {
			const { form } = this;
			const urlBuilder = new URL(url);
			const params = new URLSearchParams({
				'post-id': postId.toString(),
				token: token,
				'module-id': form.formElement?.getAttribute('data-js-frontend-form-id') || '',
				nonce: nonce?.toString() || '',
			});

			urlBuilder.search = params.toString();

			return urlBuilder.toString();
		})();

		try {
			const response = await fetch(url);
			const json = await response.json();

			if (!response.ok) {
				this.statusHandler.setStatus(
					SubmitStatus.Error,
					json.message ?? this.modularityFrontendFormLang?.communicationError ?? 'Communication error.',
					'vpn_key_alert',
					0,
					10000,
					this,
					this,
				);
				return null;
			}

			this.statusHandler.setStatus(SubmitStatus.Loading, 'Loading form data...', 'file_open', 100, 500);

			return json?.data ?? null;
		} catch (error: any) {
			this.statusHandler.setStatus(
				SubmitStatus.Error,
				this.modularityFrontendFormLang?.communicationError ?? 'Communication error.',
				'link_off',
				0,
				10000,
				this,
				this,
			);
			return null;
		}
	}
}

export default FormDataFetcher;
