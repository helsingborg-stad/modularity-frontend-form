import StatusRenderer from "./statusRenderer";
import StatusRendererMessageUI from "./statusRendererMessageUI";
import StatusRendererOverlayUI from "./statusRendererOverlayUI";

class StatusRendererFactory {
    public static create(formContainer: HTMLElement, modularityFrontendFormLang: ModularityFrontendFormLang): null|StatusRenderer {
        const statusIcon = formContainer.querySelector('[data-js-frontend-form-working__icon]') as HTMLElement;
        const statusTitle = formContainer.querySelector('[data-js-frontend-form-working__title]') as HTMLElement;
        const description = formContainer.querySelector('[data-js-frontend-form-working__description]') as HTMLElement;
        const progressbar = formContainer.querySelector('[data-js-frontend-form-working__progress] .c-progressbar__value') as HTMLElement;


        if (!statusIcon || !statusTitle || !description) {
            console.error("Status icon|title|description|progressbar element not found in the form container.");
            return null;
        }

        return new StatusRenderer(
            formContainer,
            new StatusRendererMessageUI(
                modularityFrontendFormLang,
                statusIcon,
                statusTitle,
                description,
                progressbar
            ),
            new StatusRendererOverlayUI(
                formContainer
            )
        );
    }
}

export default StatusRendererFactory;