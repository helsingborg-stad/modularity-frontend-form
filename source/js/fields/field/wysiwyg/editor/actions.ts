class Actions implements ActionsInterface {
    private isExecuting: boolean = false;
    private defaultActions: EditorActions = {};
    constructor(private modularityFrontend: ModularityFrontendFormLang) {
        this.defaultActions = this.setDefaultActions();
    }

    public getDefaultParagraphSeparatorString(): string {
        return 'defaultParagraphSeparator';
    }

    public getFormatBlock(): string {
        return 'formatBlock';
    }

    public createIcon(name: string): string {
        return `<span class="c-icon c-icon--size-md material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined" data-material-symbol="${name}"></span>`;
    }
    
    public queryCommandState(command: string) {
        return document.queryCommandState(command);
    }

    public queryCommandValue(command: string): string {
        return document.queryCommandValue(command);
    }

    public exec(command: string, value?: string) {
        if (this.isExecuting) return;

        try {
            this.isExecuting = true;
            document.execCommand(command, false, value ?? undefined);
        } finally {
            this.isExecuting = false;
        }
    }

    public getDefaultActions(): EditorActions {
        return this.defaultActions;
    }

    private setDefaultActions(): EditorActions {
        return {
            bold: {
                icon: this.createIcon('format_bold'),
                title: this.modularityFrontend.bold ?? "Bold",
                state: () => this.queryCommandState("bold"),
                result: () => this.exec("bold"),
            },
            italic: {
                icon: this.createIcon('format_italic'),
                title: this.modularityFrontend.italic ?? "Italic",
                state: () => this.queryCommandState("italic"),
                result: () => this.exec("italic"),
            },
            underline: {
                icon: this.createIcon('format_underlined'),
                title: this.modularityFrontend.underline ?? "Underline",
                state: () => this.queryCommandState("underline"),
                result: () => this.exec("underline"),
            },
            strikethrough: {
                icon: this.createIcon('format_strikethrough'),
                title: this.modularityFrontend.strikeThrough ?? "Strike-through",
                state: () => this.queryCommandState("strikeThrough"),
                result: () => this.exec("strikeThrough"),
            },
            heading2: {
                icon: this.createIcon('format_h1'),
                title: this.modularityFrontend.heading ?? "Heading 2",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "h2",
                result: () => {
                    const current = this.queryCommandValue(this.getFormatBlock())?.toLowerCase();
                    this.exec(this.getFormatBlock(), current === "h2" ? "<p>" : "<h2>");
                },
            },
            heading3: {
                icon: this.createIcon('format_h2'),
                title: this.modularityFrontend.subheading ?? "Heading 3",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "h3",
                result: () => {
                    const current = this.queryCommandValue(this.getFormatBlock())?.toLowerCase();
                    this.exec(this.getFormatBlock(), current === "h3" ? "<p>" : "<h3>");
                },
            },
            quote: {
                icon: this.createIcon('format_quote'),
                title: this.modularityFrontend.blockquote ?? "Quote",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "blockquote",
                result: () => this.exec(this.getFormatBlock(), "<blockquote>"),
            },
            olist: {
                icon: this.createIcon('format_list_numbered'),
                title: this.modularityFrontend.olist ?? "Ordered List",
                state: () => this.queryCommandState("insertOrderedList"),
                result: () => this.exec("insertOrderedList"),
            },
            ulist: {
                icon: this.createIcon('format_list_bulleted'),
                title: this.modularityFrontend.ulist ?? "Unordered List",
                state: () => this.queryCommandState("insertUnorderedList"),
                result: () => this.exec("insertUnorderedList"),
            },
            link: {
                icon: this.createIcon('link'),
                title: this.modularityFrontend.link ?? "Link",
                result: () => {
                    const url = window.prompt("Enter the link URL");
                    if (url) this.exec("createLink", url);
                },
            }
        }
    }
}

export default Actions;
