import BasicLayoutUI from "./basicUi";

class SelectableValuesLayoutUI extends BasicLayoutUI implements SelectableValuesLayoutUIInterface {
    private readonly OPTIONS_TARGET = '[data-name="options"] .acf-row:not(.acf-clone) input';
    private readonly OPTION_TARGET = '[data-name="name"] input';
    private readonly OPTION_KEY_ATTRIBUTE = 'data-option-key';
    private readonly OPTION_PREVIOUS_KEY_ATTRIBUTE = 'data-previous-option-key';
    private readonly OPTION_ID_COUNTER_ATTRIBUTE = 'data-option-id';
    private optionInputTimeouts = new WeakMap<HTMLInputElement, number>();

    constructor(layoutData: LayoutData) {
        super(layoutData);
    }

    public getOptionsFromDOM(): HTMLInputElement[] {
        return Array.from(
            this.layoutData.layout.querySelectorAll<HTMLInputElement>(this.OPTIONS_TARGET)
        );
    }

    public getOptionInputFromElement(element: HTMLElement): HTMLInputElement | null {
        return element.querySelector<HTMLInputElement>(this.OPTION_TARGET);
    }

    public bindOptionInputToKeyUpdate(option: HTMLInputElement, debounceMs: number, getNewKey: (option: HTMLInputElement) => string): void {
        this.onOptionInput(option, () => {
            clearTimeout(this.optionInputTimeouts.get(option));

            const timeoutId = window.setTimeout(() => {
                const previousKey = this.getCurrentOptionKey(option);
                const newKey = getNewKey(option);
                this.updateOptionKey(option, newKey, previousKey);
                this.dispatchSelectableUpdate();
            }, debounceMs);

            this.optionInputTimeouts.set(option, timeoutId);
        });
    }

    public setOptionAttributes(option: HTMLInputElement, key: string, previousKey: string, idCounter: string): void {
        option.setAttribute(this.OPTION_KEY_ATTRIBUTE, key);
        option.setAttribute(this.OPTION_PREVIOUS_KEY_ATTRIBUTE, previousKey);
        option.setAttribute(this.OPTION_ID_COUNTER_ATTRIBUTE, idCounter);
    }

    public updateOptionKey(option: HTMLInputElement, newKey: string, previousKey: string): void {
        option.setAttribute(this.OPTION_KEY_ATTRIBUTE, newKey);
        option.setAttribute(this.OPTION_PREVIOUS_KEY_ATTRIBUTE, previousKey);
    }

    public getCurrentOptionKey(option: HTMLInputElement): string {
        return option.getAttribute(this.OPTION_KEY_ATTRIBUTE) ?? '';
    }

    public getOptionIdCounter(option: HTMLInputElement): string | null {
        return option.getAttribute(this.OPTION_ID_COUNTER_ATTRIBUTE);
    }

    public dispatchSelectableUpdate(): void {
        this.layoutData.layout.dispatchEvent(new CustomEvent('layout:selectable', {
            detail: { layoutId: this.layoutData.layoutId }
        }));
    }

    public getOptionAttributes(option: HTMLInputElement): OptionValues {
        return {
            key: option.getAttribute(this.OPTION_KEY_ATTRIBUTE) ?? '',
            previousKey: option.getAttribute(this.OPTION_PREVIOUS_KEY_ATTRIBUTE) ?? '',
            label: option.value
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
