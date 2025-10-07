import { exec } from "./defaultActions";
import Editor from "./editor";

class ContentArea implements ContentAreaInterface {
    private contentArea!: HTMLElement;
    private formatBlock = "formatBlock";
    private defaultParagraphSeparator = "p";
    private defaultParagraphSeparatorString = "defaultParagraphSeparator";

    constructor(
        private config: EditorConfigInterface,
        private editor: Editor
    ) {}

    public getElement(): HTMLElement {
        return this.contentArea;
    }

    public appendContentArea(): HTMLElement {
        const div = document.createElement("div");
        div.className = this.config.getClasses().content;
        div.contentEditable = "true";
        this.config.getElement().appendChild(div);
        this.contentArea = div;
        return div;
    }

    public setupChangeListener(): void {
        this.contentArea.addEventListener("input", this.handleInput.bind(this));
        this.contentArea.addEventListener("keydown", this.handleKeyDown.bind(this));
        exec(this.defaultParagraphSeparatorString, this.defaultParagraphSeparator);
    }

    /** ----------------------------
     * Event Handlers
     * ---------------------------- */
    private handleInput(event: Event): void {
        const el = event.target as HTMLElement;
        const firstChild = el.firstChild;
        const shouldCleanFont = this.shouldCleanFont(el, firstChild);

        if (shouldCleanFont) {
            this.cleanFontTags(el);
            this.restoreCaretToEnd(el);
        }

        this.ensureParagraphBlock(el, firstChild);

        this.editor.runChangeListeners(el.innerHTML);
    }

    private handleKeyDown(event: KeyboardEvent): void {
        if (event.key === "Enter") {
            setTimeout(() => exec("formatBlock", `<${this.defaultParagraphSeparator}>`), 0);
        }
    }

    private shouldCleanFont(el: HTMLElement, firstChild: ChildNode | null): boolean {
        return firstChild !== null || el.innerHTML === "<br>";
    }

    private cleanFontTags(el: HTMLElement): void {
        el.innerHTML = el.innerHTML.replace(/<\/?font[^>]*>/gi, "");
    }

    private ensureParagraphBlock(el: HTMLElement, firstChild: ChildNode | null): void {
        if (firstChild && firstChild.nodeType === 3) {
            exec(this.formatBlock, `<${this.defaultParagraphSeparator}>`);
        } else if (
            !firstChild ||
            el.innerHTML === "<br>" ||
            el.innerHTML === `<${this.defaultParagraphSeparator}><br></${this.defaultParagraphSeparator}>`
        ) {
            el.innerHTML = "";
        }
    }

    private restoreCaretToEnd(el: HTMLElement): void {
        const range = document.createRange();
        const selection = window.getSelection();
        range.selectNodeContents(el);
        range.collapse(false);
        selection?.removeAllRanges();
        selection?.addRange(range);
    }
}

export default ContentArea;