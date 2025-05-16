import CustomInvalidValidationMessageItem from './customInvalidValidationMessageItem';

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

        document.querySelectorAll(this.target).forEach(item => {
            new CustomInvalidValidationMessageItem(
                item as HTMLInputElement|HTMLSelectElement,
                this.validationKeys,
                this.attributePrefixCamelCased
            )
        });
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