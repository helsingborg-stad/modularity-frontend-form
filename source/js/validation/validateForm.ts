class ValidateForm {
    private forms: NodeListOf<HTMLFormElement>;
    constructor(
    ) {
        this.forms = document.querySelectorAll('form:not([data-js-no-validate]):not([no-validate]');

        this.forms.forEach(form => {
            const inputs = form.querySelectorAll('input');

            inputs.forEach((input: HTMLInputElement) => {
                input.addEventListener('invalid', () => {
                    const validity = input.validity;

                    if (validity.valueMissing) {
                        const customMessage = input.getAttribute('data-js-value-missing');

                        if (customMessage) {
                            input.validationMessage = customMessage;
                        }
                    }

                    if (validity.typeMismatch) {
                        const customMessage = input.getAttribute('data-js-type-mismatch');

                        if (customMessage) {
                            input.validationMessage = customMessage;
                        }
                    }

                    input.dispatchEvent(new CustomEvent('invalid-validation', {
                        detail: { message: input.validationMessage },
                    }));
                });
            });
        });
    }
}

export default ValidateForm;