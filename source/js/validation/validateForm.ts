class ValidateForm {
    private attributePrefix: string = 'data-js-validation-message-';
    private attributePrefixCamelCased: string = 'jsValidationMessage';

    constructor(
    ) {
        const inputsWithCustomValidationMessage = Array.from(document.querySelectorAll('input,select')).filter((input) => {
            return Array.from(input.attributes).some((attr) => {
                return attr.name.startsWith(this.attributePrefix);
            }
            );
        })

        inputsWithCustomValidationMessage.forEach((input) => {

            input.addEventListener('invalid', (event) => {
                const target = event.target as HTMLInputElement | HTMLSelectElement;

                for (const key of Object.getOwnPropertyNames(Object.getPrototypeOf(target.validity))) {
                    const propertyState = target.validity[key as keyof ValidityState];
                    const keyWithFirstUpperCase = key.charAt(0).toUpperCase() + key.slice(1);

                    if (propertyState === false) {
                        continue;
                    }

                    if (target.dataset.hasOwnProperty(this.attributePrefixCamelCased + keyWithFirstUpperCase)) {
                        target.setCustomValidity(target.dataset[this.attributePrefixCamelCased + keyWithFirstUpperCase] ?? target.validationMessage);
                    }
                }
            });
        });
    }
}

export default ValidateForm;