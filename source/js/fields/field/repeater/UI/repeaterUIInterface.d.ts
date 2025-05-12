interface RepeaterUIInterface {
    init(): void;
    getRowCount(): number;
}

interface RowBuilderInterface {
    createRow(id?: string): HTMLElement;
}