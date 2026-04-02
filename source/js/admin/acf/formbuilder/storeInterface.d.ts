interface FormBuilderStoreInterface {
    add(id: string, layout: BasicLayoutInterface | RadioLayoutInterface): void;
    remove(id: string): void;
    get(id: string): BasicLayoutInterface | RadioLayoutInterface | null;
    getAll(): (BasicLayoutInterface | RadioLayoutInterface)[];
}