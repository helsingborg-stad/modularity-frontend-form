import Editor from './editor';

class ContentArea implements ContentAreaInterface {
	private contentArea!: HTMLElement;
	private defaultParagraphSeparator = 'div';
	private defaultParagraphSeparatorString = 'defaultParagraphSeparator';
	private shouldCleanFontTags = false;

	constructor(
		private config: EditorConfigInterface,
		private editor: Editor,
		private actions: ActionsInterface,
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
		this.contentArea.addEventListener('input', this.handleInput.bind(this));
		this.actions.exec(this.defaultParagraphSeparatorString, this.defaultParagraphSeparator);
	}

	private handleInput(event: Event): void {
		const el = event.target as HTMLElement;

		if (this.shouldCleanFontTags) {
			this.cleanFontTags(el);
			this.restoreCaretToEnd(el);
			this.shouldCleanFontTags = false;
		}

		this.editor.runChangeListeners(el.innerHTML);
	}

	private cleanFontTags(el: HTMLElement): void {
		el.innerHTML = el.innerHTML.replace(/<\/?font[^>]*>/gi, '');
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
