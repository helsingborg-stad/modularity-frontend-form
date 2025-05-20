class RowBuilder implements RowBuilderInterface {
    private replacement: string = 'INDEX_REPLACE';

    constructor(private template: HTMLTemplateElement, private templateContainer: HTMLElement) {}

    public createRow(id: string, includeRemoveRowButton: boolean = true): HTMLElement {
        let rowHtml = this.template.innerHTML;
        rowHtml = rowHtml.replaceAll(this.replacement, id);

        return this.appendRow(rowHtml, includeRemoveRowButton);
    }

    private appendRow(rowHtml: string, includeRemoveRowButton: boolean = true): HTMLElement {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = rowHtml;
        const row = tempDiv.firstElementChild as HTMLElement;

        if (!includeRemoveRowButton) {
            row.classList.add('no-remove');
        }
      
        this.templateContainer.appendChild(row);
        requestAnimationFrame(() => {
            row.style.maxHeight = row.scrollHeight + 'px';
        });

        row.addEventListener('transitionend', () => {
            row.classList.add('animate-show');
            row.style.maxHeight = 'unset';
        }, { once: true });

        return row;
    }

    public deleteRow(row: HTMLElement): void {
        row.style.maxHeight = row.scrollHeight + 'px';

        requestAnimationFrame(() => {
            row.classList.add('animate-remove');
            row.style.maxHeight = '0px';
        });

        row.addEventListener('transitionend', () => {
            row.remove();
        }, { once: true });
    }
}

export default RowBuilder;