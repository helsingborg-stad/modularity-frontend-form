import BasicLayout from "./basic";
declare const acf: any;

class SelectableValuesLayout extends BasicLayout implements SelectableValuesLayoutInterface {
    private options: options = {};
    private i: number = 0;
    private OPTIONS_TARGET = '[data-name="options"] .acf-row:not(.acf-clone) input';
    private OPTION_TARGET = '[data-name="name"] input';
    private OPTION_ID_ATTRIBUTE = 'data-option-key';
    private OPTION_PREVIOUS_ID_ATTRIBUTE = 'data-previous-option-key';
    private OPTION_ID_COUNTER_ATTRIBUTE = 'data-option-id';

    constructor(...args: ConstructorParameters<typeof BasicLayout>) {
        super(...args);
    }

    public initOptions(): void {
        this.addOptionsFromDOM();

        acf.addAction('append', ($el: JQuery<HTMLElement>) => {
            if (!this.checkIfElementIsWithinTheFormbuilderField($el)) {
                return;
            }

            const option = $el[0].querySelector(this.OPTION_TARGET) as HTMLInputElement;

            if (!option) {
                return;
            }

            this.initAddedOption(option);
        });
    }

    /**
     * Sets up a debounced input listener on the option input element.
     * Updates option key attributes only after the user has stopped typing for 1 second.
     *
     * @param option The input element to attach the listener to.
     */
    private setListenersOnOptionInput(option: HTMLInputElement): void {
        let debounceTimeout: number | undefined;

        option.addEventListener('input', () => {
            if (debounceTimeout) {
                clearTimeout(debounceTimeout);
            }

            debounceTimeout = window.setTimeout(() => {
                const previousKey = option.getAttribute(this.OPTION_ID_ATTRIBUTE);
                const newKey = this.getOptionKey(option);
                option.setAttribute(this.OPTION_ID_ATTRIBUTE, newKey);
                option.setAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE, previousKey as string);

            }, 1000);
        });
    }

    private addOptionsFromDOM(): void {
        const options = this.layoutData.layout.querySelectorAll(this.OPTIONS_TARGET);
        options.forEach((option: Element) => {
            if (!(option instanceof HTMLInputElement)) {
                return;
            }

            this.initAddedOption(option);
        });
    }

    private initAddedOption(option: HTMLInputElement): void {
        const key = this.getOptionKey(option);
        option.setAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE, key);
        option.setAttribute(this.OPTION_ID_ATTRIBUTE, key);
        const idCounter = (this.i++).toString();
        option.setAttribute(this.OPTION_ID_COUNTER_ATTRIBUTE, idCounter);
        this.options[idCounter] = option;
        this.setListenersOnOptionInput(option);
    }

    private getOptionKey(option: HTMLInputElement): string {
        return option.value || `option-${this.i++}`;
    }

    public getValues(): Array<OptionValues> {
        return Object.entries(this.options).map(([key, element]) => ({
            key: element.getAttribute(this.OPTION_ID_ATTRIBUTE) ?? '',
            previousKey: element.getAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE) ?? '',
            label: element.value ?? ''
        }));
    }

    private checkIfElementIsWithinTheFormbuilderField($el: JQuery<HTMLElement>): boolean {
        return this.layoutData.layout.contains($el[0]);
    }
}

export default SelectableValuesLayout;