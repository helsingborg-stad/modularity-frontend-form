import Editor from "./editor";

class ContentArea implements ContentAreaInterface {
	private contentArea!: HTMLElement;
	private formatBlock = "formatBlock";
	private defaultParagraphSeparator = "p";
	private defaultParagraphSeparatorString = "defaultParagraphSeparator";
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
		this.actions.exec(
			this.defaultParagraphSeparatorString,
			this.defaultParagraphSeparator,
		);
	}

	private handleInput(event: Event): void {
		const el = event.target as HTMLElement;
		const firstChild = el.firstChild;

		if (this.shouldCleanFontTags) {
			this.cleanFontTags(el);
			this.restoreCaretToEnd(el);
			this.shouldCleanFontTags = false;
		}

		this.ensureParagraphBlock(el, firstChild);

		this.editor.runChangeListeners(el.innerHTML);
	}

	private handleKeyDown(event: KeyboardEvent): void {
		if (event.key === "Enter") {
			const activeActions = this.actions.getActiveActions();
			if (["olist", "ulist"].some((action) => activeActions.includes(action))) {
				return;
			}

			setTimeout(
				() =>
					this.actions.exec(
						"formatBlock",
						`<${this.defaultParagraphSeparator}>`,
					),
				0,
			);
		}
	}

	private cleanFontTags(el: HTMLElement): void {
		el.innerHTML = el.innerHTML.replace(/<\/?font[^>]*>/gi, "");
	}

	private ensureParagraphBlock(
		el: HTMLElement,
		firstChild: ChildNode | null,
	): void {
		if (firstChild && firstChild.nodeType === 3) {
			this.actions.exec(
				this.formatBlock,
				`<${this.defaultParagraphSeparator}>`,
			);
		} else if (this.isBlockEmpty(el)) {
			el.innerHTML = "";
			this.shouldCleanFontTags = true;
		}
	}

	private isBlockEmpty(el: HTMLElement): boolean {
		return (
			!el.firstChild ||
			el.innerHTML === "<br>" ||
			el.innerHTML ===
				`<${this.defaultParagraphSeparator}><br></${this.defaultParagraphSeparator}>` ||
			(el.childNodes.length === 1 &&
				el.firstChild?.nodeName.toLowerCase() ===
					this.defaultParagraphSeparator &&
				(el.firstChild as HTMLElement).innerHTML.trim() === "<br>")
		);
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
