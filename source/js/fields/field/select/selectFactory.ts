import NullFieldFactory from "../nullField/nullFieldFactory";
import SelectConditionHandler from "./condition/selectConditionHandler";
import SelectConditionValidator from "./condition/selectConditionValidator";
import Select from "./select";

class SelectFactory {
    public static create(
        field: HTMLElement,
        name: string,
        unstructuredConditions: any
    ): FieldInterface {
        const select = field.querySelector('select') as HTMLSelectElement;
        const options = select?.querySelectorAll('option') as NodeListOf<HTMLOptionElement>;

        if (!options || options.length === 0) {
            console.error('Select field is missing select element or options');
            return NullFieldFactory.create(field, 'select', name, unstructuredConditions);
        }

        return new Select(
            field,
            select,
            options,
            name,
            new SelectConditionValidator(),
            new SelectConditionHandler(unstructuredConditions)
        )
    }
}

export default SelectFactory;