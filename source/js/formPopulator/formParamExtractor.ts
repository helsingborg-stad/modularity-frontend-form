class FormParamExtractor {
	constructor(private form: HTMLFormElement) {}
	public tryExtractFormParams(): FormParams | null {
		const params = new URLSearchParams(window.location.search);
		const postId = Number(params.get('postId'));
		const token = params.get('token') ?? '';
		const moduleId = Number(this.form.getAttribute('data-js-frontend-form-id') || '');

		if (!isNaN(postId) && token.length === 32) {
			return {
				moduleId: moduleId as number,
				postId: postId as number,
				token: token as Token32,
			};
		}

		return null;
	}
}

export default FormParamExtractor;
