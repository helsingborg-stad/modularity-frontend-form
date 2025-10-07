import EditorConfig from "./editorConfig";
import ContentArea from "./contentArea";
import Actionbar from "./actionbar";

class Editor implements EditorInterface {
    private contentArea!: ContentAreaInterface;
    private actionbar!: ActionbarInterface;
    private listeners: ((html: string) => void)[] = [];
    constructor(
        private config: EditorConfig,
    ) {}

    public init(): EditorInterface {
        this.contentArea = new ContentArea(this.config, this);
        this.actionbar = new Actionbar(this.config, this.contentArea);
        this.actionbar.appendActionbar();
        this.contentArea.appendContentArea();
        this.contentArea.setupChangeListener();
        this.actionbar.addActionButtons(this.config.getEditorActions());

        return this;
    }

    public addChangeListeners(listener: (html: string) => void): void {
        this.listeners.push(listener);
    }

    public runChangeListeners(html: string): void {
        this.listeners.forEach((listener) => listener(html));
    }

    public getContentArea(): ContentAreaInterface {
        return this.contentArea;
    }

    public getActionbar(): ActionbarInterface {
        return this.actionbar;
    }

    public getContent(): string {
        return this.contentArea.getElement().innerHTML;
    }

    public hasContent(): boolean {
        const content = this.contentArea.getElement().innerHTML.trim();
        const isEmpty = !content || content === '<br>' || content === '<div><br></div>' || content === '<p><br></p>';
        return !isEmpty;
    }
}

export default Editor;