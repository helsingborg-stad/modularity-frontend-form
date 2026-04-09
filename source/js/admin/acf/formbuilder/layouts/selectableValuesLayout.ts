import BasicLayout from "./basic";
import SelectableValuesLayoutUI from "./selectableValuesLayoutUI";

class SelectableValuesLayout extends BasicLayout implements SelectableValuesLayoutInterface {
    private options: LayoutOptions = {};
    private optionCounter: number = 0;
    private keyCounter: number = 0;

    constructor(
        protected layoutData: LayoutData,
        protected layoutUI: SelectableValuesLayoutUI
    ) {
        super(layoutData, layoutUI);
    }

    public initOptions(): void {
        this.layoutUI.getOptionsFromDOM().forEach(option => this.initAddedOption(option));

        acf.addAction('append', ($el: JQuery<HTMLElement>) => {
            if (!this.layoutUI.isElementWithinLayout($el[0])) {
                return;
            }

            const option = this.layoutUI.getOptionInputFromElement($el[0]);

            if (!option) {
                return;
            }

            this.initAddedOption(option);
            this.layoutUI.dispatchSelectableUpdate();
        });
    }

    private initAddedOption(option: HTMLInputElement): void {
        const key = this.getOptionKey(option);
        const idCounter = (this.optionCounter++).toString();
        this.layoutUI.setOptionAttributes(option, key, key, idCounter);
        this.options[idCounter] = option;
        this.layoutUI.bindOptionInputToKeyUpdate(option, 1000, opt => this.getOptionKey(opt));
    }

    private getOptionKey(option: HTMLInputElement): string {
        return option.value || `option-${this.keyCounter++}`;
    }

    public getValues(): OptionValues[] {
        return Object.values(this.options).map(element => this.layoutUI.getOptionAttributes(element));
    }
}

export default SelectableValuesLayout;
