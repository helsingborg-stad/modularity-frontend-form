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

        this.applyValidation(document);
        this.listenForDomChanges();
    }

    private applyValidation(root: ParentNode): void {
        root.querySelectorAll(this.target).forEach(item => {
            const element = item as HTMLInputElement | HTMLSelectElement;
            if (!(element.dataset.validationInitialized)) {
                new CustomInvalidValidationMessageItem(
                    element,
                    this.validationKeys,
                    this.attributePrefixCamelCased
                );
                element.dataset.validationInitialized = 'true';
            }
        });
    }

    private getTarget(): string {
        return `:is(input, select, textarea):is(${
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

    private listenForDomChanges(): void {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node instanceof HTMLElement) {
                        this.applyValidation(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

export default ValidateForm;