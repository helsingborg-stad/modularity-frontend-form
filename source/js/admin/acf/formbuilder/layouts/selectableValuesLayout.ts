import BasicLayout from "./basic";

class SelectableValuesLayout extends BasicLayout implements SelectableValuesLayoutInterface {
    public getValue(): Array<{ value: string; label: string }> {
        return [{ value: 'option1', label: 'Option 1' }, { value: 'option2', label: 'Option 2' }];
    }
}

export default SelectableValuesLayout;