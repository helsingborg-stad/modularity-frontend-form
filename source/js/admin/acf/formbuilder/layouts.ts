import LayoutFactory from "./layoutFactory";
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
            }
        });

        acf.addAction('remove', ($el: JQuery<HTMLElement>) => {
            if (!this.checkIfElementIsWithinTheFormbuilderField($el)) {
                return;
            }

            this.removeLayout($el[0]);
        });

        this.updateAllConditionalSelects();
    }

    private addLayout(layoutElement: HTMLElement): BasicLayoutInterface | null {
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
    }

    private updateAllConditionalSelects(): void {
        const optionsFragment = this.createOptionsFragment();

        this.store.getAll().forEach(layout => {
            layout.updateConditionalSelectOptions(optionsFragment.cloneNode(true));
        });
    }

    public initLayouts(): void {
        this.store.getAll().forEach(layout => {
            layout.init();
        });
    }

    private createOptionsFragment(): DocumentFragment {
        const fragment = document.createDocumentFragment();

        const none = document.createElement('option');
        none.value = '';
        none.textContent = 'None';
        fragment.appendChild(none);

        this.store.getAll().forEach(layout => {
            const option = document.createElement('option');
            option.value = layout.getId();
            option.textContent = layout.getNameField().value || 'Unnamed Layout';
            fragment.appendChild(option);
        });

        return fragment;
    }

    private checkIfElementIsWithinTheFormbuilderField($el: JQuery<HTMLElement>): boolean {
        return this.field.$el[0].contains($el[0]);
    }
}

export default Layouts
