import { exec } from "./defaultActions";
import Editor from "./editor";

class ContentArea implements ContentAreaInterface {
    private contentArea!: HTMLElement;
    private formatBlock = 'formatBlock';
    private defaultParagraphSeparator = 'p';
    private defaultParagraphSeparatorString = 'defaultParagraphSeparator';

    constructor(
        private config: EditorConfigInterface,
        private editor: Editor
    ) {}

    public getElement(): HTMLElement {
        return this.contentArea;
    }

    public appendContentArea(): HTMLElement {
        const div = document.createElement('div');
        div.className = this.config.getClasses().content;
        div.contentEditable = 'true';

        this.config.getElement().appendChild(div);
        this.contentArea = div;
        return div;
    }

    public setupChangeListener(): void {
        this.contentArea.addEventListener('input', ({ target }) => {
            const firstChild = (target as HTMLElement).firstChild;
            if (firstChild && firstChild.nodeType === 3) {
                exec(this.formatBlock, `<${this.defaultParagraphSeparator}>`);
            } else if (this.contentArea.innerHTML === '<br>') {
                this.contentArea.innerHTML = '';
            }

            this.config.getOnchange()(this.contentArea.innerHTML);
        });

        this.contentArea.addEventListener('keydown', (event: KeyboardEvent) => {
            if (event.key === 'Enter') {
                setTimeout(() => exec('formatBlock', `<${this.defaultParagraphSeparator}>`), 0);
            }
        });

        exec(this.defaultParagraphSeparatorString, this.defaultParagraphSeparator);
    }
}

export default ContentArea;