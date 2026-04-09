import LayoutFactory from "./layoutFactory";
import { createConditionalValueOptionsFragment, createLayoutOptionsFragment } from "./layoutOptionFragments";
import Store from "./store";

class Layouts {
    constructor(private field: AcfField, private layoutFactory: LayoutFactory, private store: Store) { }

    public init(): void {
        const addedLayouts: BasicLayoutInterface[] = [];

        this.field.$el.find('[data-layout]:not(.acf-clone)').each((_index: number, element: HTMLElement) => {
            const layout = this.addLayout(element, false);
            if (layout) {
                addedLayouts.push(layout);
            }
        });

        this.updateAllConditionalSelects();

        addedLayouts.forEach(layout => layout.init());

        acf.addAction('append', ($el: JQuery<HTMLElement>) => {
            if (!this.field.$el[0].contains($el[0])) {
                return;
            }

            const layout = this.addLayout($el[0]);
            layout?.init();
        });

        acf.addAction('remove', ($el: JQuery<HTMLElement>) => {
            if (!this.field.$el[0].contains($el[0])) {
                return;
            }

            this.removeLayout($el[0]);
        });

        this.updateAllConditionalSelectValuesOptions();
    }

    private addLayout(layoutElement: HTMLElement, shouldUpdateConditionalSelects: boolean = true): BasicLayoutInterface | SelectableValuesLayoutInterface | null {
        const layout = this.layoutFactory.createLayout(layoutElement);

        if (!layout) {
            return null;
        }

        this.store.add(layout.getId(), layout);
        this.setLayoutEventListeners(layoutElement);

        if (shouldUpdateConditionalSelects) {
            this.updateAllConditionalSelects();
        }

        return layout;
    }

    private removeLayout(layoutElement: HTMLElement): void {
        const layoutId = (layoutElement.querySelector('[data-name="hidden_id"] input') as HTMLInputElement | null)?.value;

        if (layoutId && this.store.get(layoutId)) {
            this.store.remove(layoutId);
            this.updateAllConditionalSelects();
        }
    }

    private setLayoutEventListeners(layoutElement: HTMLElement): void {
        layoutElement.addEventListener('layout:update', () => {
            this.updateAllConditionalSelects();
        });

        layoutElement.addEventListener('layout:conditional-changed', (event: Event) => {
            const { layoutId } = (event as CustomEvent<{ layoutId: string }>).detail ?? {};
            if (layoutId) {
                this.maybeUpdateConditionalValueSelectForLayout(layoutId);
            }
        });

        layoutElement.addEventListener('layout:selectable', (event: Event) => {
            const { layoutId } = (event as CustomEvent<{ layoutId: string }>).detail ?? {};
            if (layoutId) {
                this.maybeUpdateConditionalValueSelect(layoutId);
            }
        });
    }

    private maybeUpdateConditionalValueSelect(layoutId: string): void {
        const layout = this.store.get(layoutId);

        if (!this.isSelectableValuesLayout(layout)) {
            return;
        }

        const affectedLayouts = this.store.getAll().filter(l => l.getSavedConditionalLogicValue() === layoutId);

        if (affectedLayouts.length === 0) {
            return;
        }

        const values = layout.getValues();
        const fragment = createConditionalValueOptionsFragment(values);

        affectedLayouts.forEach(l => {
            l.updateConditionalSelectValuesOptions(fragment.cloneNode(true), values);
        });
    }

    private maybeUpdateConditionalValueSelectForLayout(changedLayoutId: string): void {
        const targetId = this.store.get(changedLayoutId)?.getSavedConditionalLogicValue();
        if (targetId) {
            this.maybeUpdateConditionalValueSelect(targetId);
        }
    }

    private updateAllConditionalSelects(): void {
        const layouts = this.store.getAll();
        const fragment = createLayoutOptionsFragment(layouts);
        layouts.forEach(layout => {
            layout.updateConditionalSelectOptions(fragment.cloneNode(true));
        });
    }

    private updateAllConditionalSelectValuesOptions(): void {
        this.store.getAll().forEach(layout => {
            this.maybeUpdateConditionalValueSelect(layout.getId());
        });
    }

    private isSelectableValuesLayout(layout: BasicLayoutInterface | SelectableValuesLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && layout.getType() === 'radio' && 'getValues' in layout;
    }
}

export default Layouts;
