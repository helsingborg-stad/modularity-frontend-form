class Store implements FormBuilderStoreInterface {
    private layouts: Record<string, BasicLayoutInterface> = {};

    public add(id: string, layout: BasicLayoutInterface): void {
        this.layouts[id] = layout;
    }

    public remove(id: string): void {
        delete this.layouts[id];
    }

    public get(id: string): BasicLayoutInterface | null {
        return this.layouts[id] ?? null;
    }

    public getAll(): BasicLayoutInterface[] {
        return Object.values(this.layouts);
    }
}

export default Store;
