import AddRow from "./addRow";

class Repeater {
    constructor(
        private repeaterContainer: HTMLElement,
        private addRow: AddRow
    ) {
        this.init();
    }

    public init() {
        this.addRow.setupListener();
    }
}

export default Repeater;