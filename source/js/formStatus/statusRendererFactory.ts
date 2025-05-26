import StatusRenderer from "./statusRenderer";
import StatusRendererUI from "./statusRendererUI";

class StatusRendererFactory {
    public static create(formContainer: HTMLElement, modularityFrontendFormLang: ModularityFrontendFormLang): null|StatusRenderer {
        const statusIcon = formContainer.querySelector('[data-js-frontend-form-working__icon]') as HTMLElement;
        if (!statusIcon) {
            console.error("Status icon element not found in the form container.");
            return null;
        }

        return new StatusRenderer(
            formContainer,
            modularityFrontendFormLang,
            new StatusRendererUI(
                statusIcon
            )
        );
    }
}

export default StatusRendererFactory;