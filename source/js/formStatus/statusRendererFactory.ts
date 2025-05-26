import StatusRenderer from "./statusRenderer";
import StatusRendererUI from "./statusRendererUI";

class StatusRendererFactory {
    public static create(formContainer: HTMLElement, modularityFrontendFormLang: ModularityFrontendFormLang): null|StatusRenderer {
        const statusIcon = formContainer.querySelector('[data-js-frontend-form-working__icon]') as HTMLElement;
        const statusTitle = formContainer.querySelector('[data-js-frontend-form-working__title]') as HTMLElement;
        const descriptionEl = formContainer.querySelector('[data-js-frontend-form-working__description]') as HTMLElement;

        if (!statusIcon || !statusTitle || !descriptionEl) {
            console.error("Status icon|title|description element not found in the form container.");
            return null;
        }

        return new StatusRenderer(
            formContainer,
            new StatusRendererUI(
                modularityFrontendFormLang,
                statusIcon,
                statusTitle,
                descriptionEl
            )
        );
    }
}

export default StatusRendererFactory;