class RowBuilder implements RowBuilderInterface {
    private replacement: string = 'repeater_id';

    constructor(private template: HTMLTemplateElement, private templateContainer: HTMLElement) {}

    public createRow(id: string = 'row'): HTMLElement {
        let rowHtml = this.template.innerHTML;
        rowHtml = rowHtml.replaceAll(this.replacement, id);

        return this.appendRow(rowHtml);
    }

    private appendRow(rowHtml: string): HTMLElement {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = rowHtml;
        const row = tempDiv.firstElementChild as HTMLElement;
      
        if (row) {
            this.templateContainer.appendChild(row);
            requestAnimationFrame(() => {
                row.style.maxHeight = row.scrollHeight + 'px';
            });

            row.addEventListener('transitionend', () => {
                row.classList.add('animate-show');
            }, { once: true });

            this.removeRowListener(row);
        }

        return row;
    }

    private removeRowListener(row: HTMLElement): void {
        row.querySelector('[data-js-repeater-remove-row]')?.addEventListener('click', (e) => {
            e.preventDefault();

            requestAnimationFrame(() => {
                row.classList.add('animate-remove');
                row.style.maxHeight = '0px';
            });
    
            row.addEventListener('transitionend', () => {
                row.remove();
            }, { once: true });
        });
    }
}

export default RowBuilder;