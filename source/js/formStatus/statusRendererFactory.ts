import StatusRenderer from "./statusRenderer";
import StatusRendererButtonUIHandler from "./statusRendererButtonUIHandler";
import StatusRendererMessageUI from "./statusRendererMessageUI";
import StatusRendererOverlayUI from "./statusRendererOverlayUI";

class StatusRendererFactory {
	public static create(
		formContainer: HTMLElement,
		modularityFrontendFormLang: ModularityFrontendFormLang,
	): null | StatusRenderer {
		const statusIcon = formContainer.querySelector(
			"[data-js-frontend-form-working__icon]",
		) as HTMLElement;
		const statusTitle = formContainer.querySelector(
			"[data-js-frontend-form-working__title]",
		) as HTMLElement;
		const description = formContainer.querySelector(
			"[data-js-frontend-form-working__description]",
		) as HTMLElement;
		const progressbar = formContainer.querySelector(
			"[data-js-frontend-form-working__progress] .c-progressbar__value",
		) as HTMLElement;
		const tryAgainButton = formContainer.querySelector(
			"[data-js-frontend-form-working-try-again-button]",
		) as HTMLElement;
		const returnButton = formContainer.querySelector(
			"[data-js-frontend-form-working-return-button]",
		) as HTMLElement;

		const workingOverlay = formContainer.querySelector(
			"[data-js-frontend-form-working]",
		) as HTMLElement;

		if (
			!statusIcon ||
			!statusTitle ||
			!description ||
			!progressbar ||
			!workingOverlay ||
			!tryAgainButton ||
			!returnButton
		) {
			console.error("Not all status elements were found the form container.");
			return null;
		}

		const statusRendererOverlayUI = new StatusRendererOverlayUI(
			formContainer,
			workingOverlay,
		);

		return new StatusRenderer(
			formContainer,
			new StatusRendererMessageUI(
				modularityFrontendFormLang,
				statusIcon,
				statusTitle,
				description,
				progressbar,
			),
			statusRendererOverlayUI,
			new StatusRendererButtonUIHandler(
				returnButton,
				tryAgainButton,
				statusRendererOverlayUI,
			),
		).setup();
	}
}

export default StatusRendererFactory;
