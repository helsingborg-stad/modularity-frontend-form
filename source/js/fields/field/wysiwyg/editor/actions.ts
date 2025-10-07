class Actions implements ActionsInterface {
    private isExecuting: boolean = false;
    private defaultActions: EditorActions = this.setDefaultActions();
    constructor() {}

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
                title: "Bold",
                state: () => this.queryCommandState("bold"),
                result: () => this.exec("bold"),
            },
            italic: {
                icon: this.createIcon('format_italic'),
                title: "Italic",
                state: () => this.queryCommandState("italic"),
                result: () => this.exec("italic"),
            },
            underline: {
                icon: this.createIcon('format_underline'),
                title: "Underline",
                state: () => this.queryCommandState("underline"),
                result: () => this.exec("underline"),
            },
            strikethrough: {
                icon: this.createIcon('format_strikethrough'),
                title: "Strike-through",
                state: () => this.queryCommandState("strikeThrough"),
                result: () => this.exec("strikeThrough"),
            },
            heading1: {
                icon: this.createIcon('format_h1'),
                title: "Heading 1",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "h1",
                result: () => {
                    const current = this.queryCommandValue(this.getFormatBlock())?.toLowerCase();
                    this.exec(this.getFormatBlock(), current === "h1" ? "<p>" : "<h1>");
                },
            },
            heading2: {
                icon: this.createIcon('format_h2'),
                title: "Heading 2",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "h2",
                result: () => {
                    const current = this.queryCommandValue(this.getFormatBlock())?.toLowerCase();
                    this.exec(this.getFormatBlock(), current === "h2" ? "<p>" : "<h2>");
                },
            },
            paragraph: {
                icon: this.createIcon('format_paragraph'),
                title: "Paragraph",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "p",
                result: () => this.exec(this.getFormatBlock(), "<p>"),
            },
            quote: {
                icon: this.createIcon('format_quote'),
                title: "Quote",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "blockquote",
                result: () => this.exec(this.getFormatBlock(), "<blockquote>"),
            },
            olist: {
                icon: this.createIcon('format_list_numbered'),
                title: "Ordered List",
                state: () => this.queryCommandState("insertOrderedList"),
                result: () => this.exec("insertOrderedList"),
            },
            ulist: {
                icon: this.createIcon('format_list_bulleted'),
                title: "Unordered List",
                state: () => this.queryCommandState("insertUnorderedList"),
                result: () => this.exec("insertUnorderedList"),
            },
            code: {
                icon: this.createIcon('code'),
                title: "Code",
                state: () => this.queryCommandValue(this.getFormatBlock())?.toLowerCase() === "pre",
                result: () => this.exec(this.getFormatBlock(), "<pre>"),
            },
            link: {
                icon: this.createIcon('link'),
                title: "Link",
                result: () => {
                    const url = window.prompt("Enter the link URL");
                    if (url) this.exec("createLink", url);
                },
            }
        }
    }
}

export default Actions;
