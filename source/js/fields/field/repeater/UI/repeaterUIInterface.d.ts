interface RepeaterUIInterface {
    init(repeater: RepeaterInterface, conditionBuilder: ConditionBuilderInterface): void;
    getRowCount(): number;
    getRowIndex(): number;
    addRowChangeListener(rowCountChangeListener: RowCountChangeListener): void;
}

interface RowBuilderInterface {
    createRow(id: string): HTMLElement;
    deleteRow(row: HTMLElement): void;
}

type RemoveRepeaterRowListeners = (id: string) => void;
type RowCountChangeListener = (rowCount: number) => void;

type RowFieldsObject = {
    [key: string]: FieldsObject;
}
