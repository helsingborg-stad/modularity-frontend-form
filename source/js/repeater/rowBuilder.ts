class RowBuilder {
    private replacement: string = 'repeater_id';

    constructor(private template: HTMLTemplateElement, private templateContainer: HTMLElement) {}

    public createRow(id: string = 'row') {
        let rowHtml = this.template.innerHTML;
        rowHtml = rowHtml.replaceAll(this.replacement, id);

        this.appendRow(rowHtml);
    }

    private appendRow(rowHtml: string): void {
        console.log(rowHtml);
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = rowHtml;
        const row = tempDiv.firstElementChild;


        if (row) {
            this.removeRowListener(row);
            this.templateContainer.appendChild(row);
        }
    }

    private removeRowListener(row: Element): void {
        row.querySelector('[data-js-repeater-remove-row]')?.addEventListener('click', (e) => {
            e.preventDefault();
            row.remove();
        });
    }
}

export default RowBuilder;