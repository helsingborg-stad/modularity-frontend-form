class Store implements FormBuilderStoreInterface {
    private layouts: Record<string, BasicLayoutInterface | SelectableValuesLayoutInterface> = {};

    public add(id: string, layout: BasicLayoutInterface | SelectableValuesLayoutInterface): void {
        this.layouts[id] = layout;
    }

    public remove(id: string): void {
        delete this.layouts[id];
    }

    public get(id: string): BasicLayoutInterface | SelectableValuesLayoutInterface | null {
        return this.layouts[id] ?? null;
    }

    public getAll(): (BasicLayoutInterface | SelectableValuesLayoutInterface)[] {
        return Object.values(this.layouts);
    }
}

export default Store;
