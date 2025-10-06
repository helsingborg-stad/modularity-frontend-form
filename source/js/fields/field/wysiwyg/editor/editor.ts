import EditorConfig from "./editorConfig";
import ContentArea from "./contentArea";
import Actionbar from "./actionbar";

class Editor {
    private contentArea!: ContentAreaInterface;
    private actionbar!: ActionbarInterface;
    constructor(
        private config: EditorConfig,
    ) {}

    public init(): Editor {
        this.contentArea = new ContentArea(this.config);
        this.actionbar = new Actionbar(this.config, this.contentArea);
        this.actionbar.appendActionbar();
        this.contentArea.appendContentArea();
        this.contentArea.setupChangeListener();
        this.actionbar.addActionButtons(this.config.getEditorActions());

        return this;
    }

    public getContentArea(): ContentAreaInterface {
        return this.contentArea;
    }

    public getActionbar(): ActionbarInterface {
        return this.actionbar;
    }
}

export default Editor;