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
            this.layoutUI.dispatchSelectableUpdate();
        });

        acf.addAction('remove', ($el: JQuery<HTMLElement>) => {
            if (!this.layoutUI.isElementWithinLayout($el[0])) {
                return;
            }

            const option = this.layoutUI.getOptionInputFromElement($el[0]);

            if (!option) {
                return;
            }

            this.removeOption(option);
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
        this.layoutUI.bindOptionInputToKeyUpdate(option, 1000, optionInput => this.getOptionKey(optionInput));
    }

    private getOptionKey(option: HTMLInputElement): string {
        return option.value || `option-${this.i++}`;
    }

    private removeOption(option: HTMLInputElement): void {
        const idCounter = this.layoutUI.getOptionIdCounter(option);

        if (!idCounter) {
            return;
        }

        delete this.options[idCounter];
        this.layoutUI.dispatchSelectableUpdate();
    }

    public getValues(): Array<OptionValues> {
        return Object.entries(this.options).map(([, element]) =>
            this.layoutUI.getOptionAttributes(element)
        );
    }
}

export default SelectableValuesLayout;
