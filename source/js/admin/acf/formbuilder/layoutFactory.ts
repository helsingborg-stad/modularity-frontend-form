import BasicLayout from "./layouts/basic";
import BasicLayoutUI from "./layouts/basicUi";
import RadioLayout from "./layouts/selectableValuesLayout";

class LayoutFactory {
    constructor(private store: FormBuilderStoreInterface) { }

    public createLayout(layout: HTMLElement): BasicLayoutInterface | null {
        const layoutData = this.getLayoutData(layout);

        if (!layoutData) {
            return null;
        }

        if (layoutData.layoutType === 'radio') {
            return new RadioLayout(layoutData, new BasicLayoutUI(layoutData));
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
        const type = layout.getAttribute('data-layout');

        if (!type) {
            return;
        }

        return type.replace('formbuilder-', '');
    }

    private findAndMaybeSetLayoutId(layout: HTMLElement): string | undefined {
        const conditionalLogicFields = layout.querySelector('[data-name="hidden_id"] input') as HTMLInputElement | null;

        if (!conditionalLogicFields) {
            return;
        }

        let id = conditionalLogicFields.value;

        if (!id) {
            id = 'hidden-id-' + Math.random().toString(36).substr(2, 9);
            conditionalLogicFields.value = id;
        }

        return id;
    }
}

export default LayoutFactory;