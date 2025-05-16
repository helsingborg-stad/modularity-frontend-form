class ValidateForm {
    private attributePrefix: string = 'data-js-validation-message-';
    private attributePrefixCamelCased: string = 'jsValidationMessage';
    private validationKeys: string[] = [];
    private target: string = '';

    private excludeKeys: string[] = [
        'valid',
        'constructor'
    ];

    constructor(
    ) {
        this.validationKeys = this.getValidationKeys();
        this.target = this.getTarget();

        console.log(this.target);

        // inputsWithCustomValidationMessage.forEach((input) => {

        //     input.addEventListener('invalid', (event) => {
        //         const target = event.target as HTMLInputElement | HTMLSelectElement;

        //         for (const key of Object.getOwnPropertyNames(Object.getPrototypeOf(target.validity))) {
        //             const propertyState = target.validity[key as keyof ValidityState];
        //             const keyWithFirstUpperCase = key.charAt(0).toUpperCase() + key.slice(1);
        //             const datasetKey = this.attributePrefixCamelCased + keyWithFirstUpperCase;

        //             if (propertyState === false) {
        //                 continue;
        //             }

        //             if (datasetKey in target.dataset) {
        //                 target.setCustomValidity(
        //                     target.dataset[datasetKey] ?? target.validationMessage
        //                 );
        //                 break; // stop after first match
        //             }
        //         }
        //     });
        // });
    }

    private getTarget(): string {
        return `:is(input, select):is(${
            this.validationKeys
                .map(key =>
                    `[${this.attributePrefix}${key.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase()}]`
                )
                .join(', ')
        })`;
    }

    private getValidationKeys(): string[] {
        return Object.getOwnPropertyNames(
            Object.getPrototypeOf(document.createElement('input').validity)
        ).filter((key) => !this.excludeKeys.includes(key));
    }
}

export default ValidateForm;