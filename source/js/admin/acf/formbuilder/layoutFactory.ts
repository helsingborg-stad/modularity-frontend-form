import BasicLayout from "./layouts/basic";
import BasicLayoutUI from "./layouts/basicUi";
import RadioLayout from "./layouts/selectableValuesLayout";
import SelectableValuesLayoutUI from "./layouts/selectableValuesLayoutUI";

class LayoutFactory {
    constructor(private store: FormBuilderStoreInterface) { }

    public createLayout(layout: HTMLElement): BasicLayoutInterface | null {
        const layoutData = this.getLayoutData(layout);

        if (!layoutData) {
            return null;
        }

        if (layoutData.layoutType === 'radio') {
            const selectableValuesLayout = new RadioLayout(layoutData, new SelectableValuesLayoutUI(layoutData));
            selectableValuesLayout.initOptions();
            return selectableValuesLayout;
        }

        return new BasicLayout(layoutData, new BasicLayoutUI(layoutData));
    }

    private getLayoutData(layout: HTMLElement): LayoutData | null {
        const layoutId = this.findAndMaybeSetLayoutId(layout);
        const layoutType = this.getLayoutType(layout);
        const name = this.getNameField(layout);
        const conditionalSelect = this.getConditionalSelect(layout);
        const conditionalOperatorSelect = this.getConditionalOperatorSelect(layout);
        const conditionalLogicValueSelect = this.getConditionalValueSelect(layout);
        const conditionalLogicSaveField = this.getHiddenConditionalLogicField(layout);

        if (!layoutId || !layoutType || !name || !conditionalSelect || !conditionalLogicSaveField || !conditionalOperatorSelect || !conditionalLogicValueSelect) {
            return null;
        }

        return {
            layout,
            layoutId,
            layoutType,
            name,
            conditionalSelect,
            conditionalLogicSaveField,
            conditionalOperatorSelect,
            conditionalLogicValueSelect,
            store: this.store
        };
    }

    private getConditionalValueSelect(layout: HTMLElement): HTMLSelectElement | null {
        return layout.querySelector('[data-name="conditional_logic_value"] select');
    }

    private getConditionalOperatorSelect(layout: HTMLElement): HTMLSelectElement | null {
        return layout.querySelector('[data-name="conditional_logic_operator"] select');
    }

    private getHiddenConditionalLogicField(layout: HTMLElement): HTMLInputElement | null {
        return layout.querySelector('[data-name="hidden_conditional_logic"] input');
    }

    private getNameField(layout: HTMLElement): HTMLInputElement | null {
        return layout.querySelector('[data-name="name"] input');
    }

    private getConditionalSelect(layout: HTMLElement): HTMLSelectElement | null {
        return layout.querySelector('[data-name="conditional_logic"] select');
    }

    private getLayoutType(layout: HTMLElement): string | undefined {
        return layout.getAttribute('data-layout')?.replace('formbuilder-', '') ?? undefined;
    }

    private findAndMaybeSetLayoutId(layout: HTMLElement): string | undefined {
        const idField = layout.querySelector('[data-name="hidden_id"] input') as HTMLInputElement | null;

        if (!idField) {
            return;
        }

        if (!idField.value) {
            idField.value = crypto.randomUUID();
        }

        return idField.value;
    }
}

export default LayoutFactory;
