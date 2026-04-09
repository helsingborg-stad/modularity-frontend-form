import BasicLayout from "./basic";
import SelectableValuesLayoutUI from "./selectableValuesLayoutUI";
declare const acf: any;

class SelectableValuesLayout extends BasicLayout implements SelectableValuesLayoutInterface {
    private options: options = {};
    private i: number = 0;

    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: SelectableValuesLayoutUI
    ) {
        super(layoutData, layoutUI);
    }

    public initOptions(): void {
        this.addOptionsFromDOM();

        acf.addAction('append', ($el: JQuery<HTMLElement>) => {
            if (!this.layoutUI.isElementWithinLayout($el[0])) {
                return;
            }

            const option = this.layoutUI.getOptionInputFromElement($el[0]);

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

        this.layoutUI.onOptionInput(option, () => {
            if (debounceTimeout) {
                clearTimeout(debounceTimeout);
            }

            debounceTimeout = window.setTimeout(() => {
                const previousKey = this.layoutUI.getCurrentOptionKey(option);
                const newKey = this.getOptionKey(option);
                this.layoutUI.updateOptionKey(option, newKey, previousKey);
            }, 1000);
        });
    }

    private addOptionsFromDOM(): void {
        this.layoutUI.getOptionsFromDOM().forEach(option => {
            this.initAddedOption(option);
        });
    }

    private initAddedOption(option: HTMLInputElement): void {
        const key = this.getOptionKey(option);
        const idCounter = (this.i++).toString();
        this.layoutUI.setOptionAttributes(option, key, key, idCounter);
        this.options[idCounter] = option;
        this.setListenersOnOptionInput(option);
    }

    private getOptionKey(option: HTMLInputElement): string {
        return option.value || `option-${this.i++}`;
    }

    public getValues(): Array<OptionValues> {
        return Object.entries(this.options).map(([, element]) =>
            this.layoutUI.getOptionAttributes(element)
        );
    }
}

export default SelectableValuesLayout;
