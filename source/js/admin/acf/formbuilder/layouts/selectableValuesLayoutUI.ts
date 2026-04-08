import BasicLayoutUI from "./basicUI";

class SelectableValuesLayoutUI extends BasicLayoutUI implements SelectableValuesLayoutUIInterface {
    private OPTIONS_TARGET = '[data-name="options"] .acf-row:not(.acf-clone) input';
    private OPTION_TARGET = '[data-name="name"] input';
    private OPTION_ID_ATTRIBUTE = 'data-option-key';
    private OPTION_PREVIOUS_ID_ATTRIBUTE = 'data-previous-option-key';
    private OPTION_ID_COUNTER_ATTRIBUTE = 'data-option-id';
    private optionInputTimeouts = new WeakMap<HTMLInputElement, number>();

    constructor(layoutData: LayoutData) {
        super(layoutData);
    }

    public getOptionsFromDOM(): HTMLInputElement[] {
        const result: HTMLInputElement[] = [];
        this.layoutData.layout.querySelectorAll(this.OPTIONS_TARGET).forEach((option: Element) => {
            if (option instanceof HTMLInputElement) {
                result.push(option);
            }
        });
        return result;
    }

    public getOptionInputFromElement(element: HTMLElement): HTMLInputElement | null {
        return element.querySelector(this.OPTION_TARGET) as HTMLInputElement | null;
    }

    public bindOptionInputToKeyUpdate(option: HTMLInputElement, debounceMs: number, getNewKey: (option: HTMLInputElement) => string): void {
        this.onOptionInput(option, () => {
            const existingTimeout = this.optionInputTimeouts.get(option);

            if (existingTimeout) {
                clearTimeout(existingTimeout);
            }

            const timeoutId = window.setTimeout(() => {
                const previousKey = this.getCurrentOptionKey(option);
                const newKey = getNewKey(option);
                this.updateOptionKey(option, newKey, previousKey);
                this.layoutData.layout.dispatchEvent(new CustomEvent('layout:selectable', {
                    detail: {
                        layoutId: this.layoutData.layoutId
                    }
                }));
            }, debounceMs);

            this.optionInputTimeouts.set(option, timeoutId);
        });
    }

    public setOptionAttributes(option: HTMLInputElement, key: string, previousKey: string, idCounter: string): void {
        option.setAttribute(this.OPTION_ID_ATTRIBUTE, key);
        option.setAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE, previousKey);
        option.setAttribute(this.OPTION_ID_COUNTER_ATTRIBUTE, idCounter);
    }

    public updateOptionKey(option: HTMLInputElement, newKey: string, previousKey: string): void {
        option.setAttribute(this.OPTION_ID_ATTRIBUTE, newKey);
        option.setAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE, previousKey);
    }

    public getCurrentOptionKey(option: HTMLInputElement): string {
        return option.getAttribute(this.OPTION_ID_ATTRIBUTE) ?? '';
    }

    public getOptionIdCounter(option: HTMLInputElement): string | null {
        return option.getAttribute(this.OPTION_ID_COUNTER_ATTRIBUTE);
    }

    public dispatchSelectableUpdate(): void {
        this.layoutData.layout.dispatchEvent(new CustomEvent('layout:selectable', {
            detail: {
                layoutId: this.layoutData.layoutId
            }
        }));
    }

    public getOptionAttributes(option: HTMLInputElement): OptionValues {
        return {
            key: option.getAttribute(this.OPTION_ID_ATTRIBUTE) ?? '',
            previousKey: option.getAttribute(this.OPTION_PREVIOUS_ID_ATTRIBUTE) ?? '',
            label: option.value ?? ''
        };
    }

    public onOptionInput(option: HTMLInputElement, callback: () => void): void {
        option.addEventListener('input', callback);
    }

    public isElementWithinLayout(element: HTMLElement): boolean {
        return this.layoutData.layout.contains(element);
    }
}

export default SelectableValuesLayoutUI;
