class CustomInvalidValidationMessageItem {
    private cachedCustomValidityMessages: { [key: string]: string } = {};
    private previousFirstInvalid: keyof ValidityState|null = null;

    constructor(
        private item: HTMLInputElement|HTMLSelectElement,
        private validationKeys: string[],
        private attributePrefixCamelCased: string
    ) {
        this.setInvalidListener();
    }

    private setInvalidListener(): void {
        this.item.addEventListener('invalid', (event: Event) => {
            const firstFaulty = this.getFirstInvalidReason(this.item as HTMLInputElement | HTMLSelectElement);

            if (firstFaulty === null) {
                return;
            }

            if (this.cachedCustomValidityMessages[firstFaulty]) {
                this.item.setCustomValidity(this.cachedCustomValidityMessages[firstFaulty]);
                return;
            }

            const keyWithFirstUpperCase = firstFaulty.charAt(0).toUpperCase() + firstFaulty.slice(1);
            const datasetKey = this.attributePrefixCamelCased + keyWithFirstUpperCase;
            const customMessage = this.item.dataset[datasetKey];

            this.cachedCustomValidityMessages[firstFaulty] = customMessage ?? this.item.validationMessage;

            if (customMessage) {
                this.item.setCustomValidity(customMessage);
            }
        });
    }

    private getFirstInvalidReason(input: HTMLInputElement | HTMLSelectElement): keyof ValidityState | null {
        const validity = input.validity;

        if (validity.valid) {
            return null;
        }

        if (this.previousFirstInvalid !== null && validity[this.previousFirstInvalid]) {
            return this.previousFirstInvalid;
        }

        for (const key of this.validationKeys as (keyof ValidityState)[]) {
            if (validity[key]) {
                this.previousFirstInvalid = key;
                return key;
            }
        }

        return null;
    }
}

export default CustomInvalidValidationMessageItem;