import BasicLayout from "./layouts/templates/basic";
import BasicLayoutUI from "./layouts/templates/basicUi";
import ConditionalLogicController from "./layouts/templates/conditionalLogicController";
import SelectableValuesLayoutUI from "./layouts/templates/selectableValuesLayoutUi";
import { type LayoutDefinition, layoutDefinitions } from "./layoutConfig";

type LayoutCreator = (layoutData: LayoutData) => BasicLayoutInterface;

class LayoutFactory {
    private readonly layoutRegistry: Record<string, LayoutCreator>;
    private readonly defaultLayoutCreator: LayoutCreator;

    constructor(private store: FormBuilderStoreInterface) {
        this.layoutRegistry = this.createLayoutRegistry(layoutDefinitions);
        this.defaultLayoutCreator = this.createBasicLayoutCreator(BasicLayout);
    }

    public createLayout(layout: HTMLElement): BasicLayoutInterface | null {
        const layoutData = this.getLayoutData(layout);

        if (!layoutData) {
            return null;
        }

        return (this.layoutRegistry[layoutData.layoutType] ?? this.defaultLayoutCreator)(layoutData);
    }

    private createLayoutRegistry(definitions: LayoutDefinition[]): Record<string, LayoutCreator> {
        return Object.fromEntries(
            definitions.map(definition => [
                definition.type,
                definition.kind === 'selectable'
                    ? this.createSelectableLayoutCreator(definition.LayoutClass)
                    : this.createBasicLayoutCreator(definition.LayoutClass)
            ])
        );
    }

    private createBasicLayoutCreator(LayoutClass: new (layoutData: LayoutData, layoutUI: BasicLayoutUI, conditionalLogicController: ConditionalLogicController) => BasicLayoutInterface): LayoutCreator {
        return (layoutData: LayoutData) => {
            const layoutUI = new BasicLayoutUI(layoutData);
            const conditionalLogicController = new ConditionalLogicController(layoutData, layoutUI);

            return new LayoutClass(layoutData, layoutUI, conditionalLogicController);
        };
    }

    private createSelectableLayoutCreator(LayoutClass: new (layoutData: LayoutData, layoutUI: SelectableValuesLayoutUI, conditionalLogicController: ConditionalLogicController) => SelectableValuesLayoutInterface): LayoutCreator {
        return (layoutData: LayoutData) => {
            const layoutUI = new SelectableValuesLayoutUI(layoutData);
            const conditionalLogicController = new ConditionalLogicController(layoutData, layoutUI);
            const layout = new LayoutClass(layoutData, layoutUI, conditionalLogicController);
            layout.initOptions();

            return layout;
        };
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
