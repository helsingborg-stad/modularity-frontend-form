import LayoutFactory from "./formbuilder/layoutFactory";
import Layouts from "./formbuilder/layouts";
import Store from "./formbuilder/store";

export function initializeFormBuilder(): void {
    const formBuilderFieldName = 'formbuilder';

    acf.addAction(`load_field/name=${formBuilderFieldName}`, (field: AcfField) => {
        const store = new Store();
        new Layouts(field, new LayoutFactory(store), store).init();
    });
}