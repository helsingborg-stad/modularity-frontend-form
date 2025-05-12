interface RepeaterUIInterface {
    init(repeater: RepeaterInterface, conditionBuilder: ConditionBuilderInterface): void;
    getRowCount(): number;
}

interface RowBuilderInterface {
    createRow(id?: string): HTMLElement;
}