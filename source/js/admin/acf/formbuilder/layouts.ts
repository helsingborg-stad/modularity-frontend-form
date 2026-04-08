import LayoutFactory from "./layoutFactory";
import LayoutOptionFragments from "./layoutOptionFragments";
import Store from "./store";

declare const acf: any;

class Layouts {
    constructor(private field: AcfField, private layoutFactory: LayoutFactory, private store: Store) { }

    public init(): void {
        const layouts = this.field.$el.find('[data-layout]:not(.acf-clone)');
        const addedLayouts: BasicLayoutInterface[] = [];
        layouts.each((index: number, element: HTMLElement) => {
            const layout = this.addLayout(element);
            if (layout) {
                addedLayouts.push(layout);
            }
        });

        addedLayouts.forEach(layout => {
            layout.init();
        });

        acf.addAction('append', ($el: JQuery<HTMLElement>) => {
            if (!this.checkIfElementIsWithinTheFormbuilderField($el)) {
                return;
            }

            const layout = this.addLayout($el[0]);

            if (layout) {
                layout.init();
                return;
            }
        });

        acf.addAction('remove', ($el: JQuery<HTMLElement>) => {
            if (!this.checkIfElementIsWithinTheFormbuilderField($el)) {
                return;
            }

            this.removeLayout($el[0]);
        });

        this.updateAllConditionalSelects();
        this.updateAllConditionalSelectValuesOptions();
    }

    private addLayout(layoutElement: HTMLElement): BasicLayoutInterface | SelectableValuesLayoutInterface | null {
        const layout = this.layoutFactory.createLayout(layoutElement);

        if (!layout) {
            return null;
        }

        this.store.add(layout.getId(), layout);
        this.setLayoutUpdateListener(layoutElement);
        this.updateAllConditionalSelects();

        return layout;
    }

    private removeLayout(layoutElement: HTMLElement): void {
        const layoutId = (layoutElement.querySelector('[data-name="hidden_id"] input') as HTMLInputElement | null)?.value;

        if (layoutId && this.store.get(layoutId)) {
            this.store.remove(layoutId);
            this.updateAllConditionalSelects();
        }
    }

    private setLayoutUpdateListener(layoutElement: HTMLElement): void {
        layoutElement.addEventListener('layout:update', () => {
            this.updateAllConditionalSelects();
        });

        layoutElement.addEventListener('layout:conditional-changed', (event: Event) => {
            const customEvent = event as CustomEvent<{ layoutId: string }>;

            if (!customEvent.detail?.layoutId) {
                return;
            }

            this.maybeUpdateConditionalValueSelectForLayout(customEvent.detail.layoutId);
        });

        layoutElement.addEventListener('layout:selectable', (event: Event) => {
            const customEvent = event as CustomEvent<{ layoutId: string }>;

            if (!customEvent.detail?.layoutId) {
                return;
            }

            this.maybeUpdateConditionalValueSelect(customEvent.detail.layoutId);
        });
    }

    private maybeUpdateConditionalValueSelect(layoutId: string): void {
        const layout = this.store.get(layoutId);

        if (!this.isSelectableValuesLayout(layout)) {
            return;
        }

        const affectedLayouts = this.store.getAll().filter(currentLayout => currentLayout.getSavedConditionalLogicValue() === layoutId);

        if (affectedLayouts.length === 0) {
            return;
        }

        const values = layout.getValues();
        const fragment = LayoutOptionFragments.createConditionalValueOptionsFragment(values);
        affectedLayouts.forEach(currentLayout => {
            currentLayout.updateConditionalSelectValuesOptions(fragment.cloneNode(true), values);
        });
    }

    private maybeUpdateConditionalValueSelectForLayout(changedLayoutId: string): void {
        const targetId = this.store.get(changedLayoutId)?.getSavedConditionalLogicValue();

        if (!targetId) {
            return;
        }

        this.maybeUpdateConditionalValueSelect(targetId);
    }

    private updateAllConditionalSelects(): void {
        const layouts = this.store.getAll();
        const optionsFragment = LayoutOptionFragments.createLayoutOptionsFragment(layouts);

        layouts.forEach(layout => {
            layout.updateConditionalSelectOptions(optionsFragment.cloneNode(true));
        });
    }

    private updateAllConditionalSelectValuesOptions(): void {
        this.store.getAll().forEach(layout => {
            this.maybeUpdateConditionalValueSelect(layout.getId());
        });
    }

    public initLayouts(): void {
        this.store.getAll().forEach(layout => {
            layout.init();
        });
    }

    private checkIfElementIsWithinTheFormbuilderField($el: JQuery<HTMLElement>): boolean {
        return this.field.$el[0].contains($el[0]);
    }

    private isSelectableValuesLayout(layout: BasicLayoutInterface | SelectableValuesLayoutInterface | null): layout is SelectableValuesLayoutInterface {
        return !!layout && layout.getType() === 'radio' && 'getValues' in layout;
    }
}

export default Layouts;
