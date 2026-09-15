interface FormBuilderStoreInterface {
    add(id: string, layout: BasicLayoutInterface | SelectableValuesLayoutInterface): void;
    remove(id: string): void;
    get(id: string): BasicLayoutInterface | SelectableValuesLayoutInterface | null;
    getAll(): (BasicLayoutInterface | SelectableValuesLayoutInterface)[];
}