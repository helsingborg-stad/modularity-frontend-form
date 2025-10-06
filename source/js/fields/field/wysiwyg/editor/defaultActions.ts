export const defaultParagraphSeparatorString = 'defaultParagraphSeparator';
export const formatBlock = 'formatBlock';

export const defaultActions: EditorActions = {
    bold: {
        icon: createIcon('format_bold'),
        title: "Bold",
        state: () => queryCommandState("bold"),
        result: () => exec("bold"),
    },
    italic: {
        icon: createIcon('format_italic'),
        title: "Italic",
        state: () => queryCommandState("italic"),
        result: () => exec("italic"),
    },
    underline: {
        icon: createIcon('format_underline'),
        title: "Underline",
        state: () => queryCommandState("underline"),
        result: () => exec("underline"),
    },
    strikethrough: {
        icon: createIcon('format_strikethrough'),
        title: "Strike-through",
        state: () => queryCommandState("strikeThrough"),
        result: () => exec("strikeThrough"),
    },
    heading1: {
        icon: createIcon('format_h1'),
        title: "Heading 1",
        state: () => queryCommandValue(formatBlock)?.toLowerCase() === "h1",
        result: () => {
            const current = queryCommandValue(formatBlock)?.toLowerCase();
            exec(formatBlock, current === "h1" ? "<p>" : "<h1>");
        },
    },
    heading2: {
        icon: createIcon('format_h2'),
        title: "Heading 2",
        state: () => queryCommandValue(formatBlock)?.toLowerCase() === "h2",
        result: () => {
            const current = queryCommandValue(formatBlock)?.toLowerCase();
            exec(formatBlock, current === "h2" ? "<p>" : "<h2>");
        },
    },
    paragraph: {
        icon: createIcon('format_paragraph'),
        title: "Paragraph",
        state: () => queryCommandValue(formatBlock)?.toLowerCase() === "p",
        result: () => exec(formatBlock, "<p>"),
    },
    quote: {
        icon: createIcon('format_quote'),
        title: "Quote",
        state: () => queryCommandValue(formatBlock)?.toLowerCase() === "blockquote",
        result: () => exec(formatBlock, "<blockquote>"),
    },
    olist: {
        icon: createIcon('format_list_numbered'),
        title: "Ordered List",
        state: () => queryCommandState("insertOrderedList"),
        result: () => exec("insertOrderedList"),
    },
    ulist: {
        icon: createIcon('format_list_bulleted'),
        title: "Unordered List",
        state: () => queryCommandState("insertUnorderedList"),
        result: () => exec("insertUnorderedList"),
    },
    code: {
        icon: createIcon('code'),
        title: "Code",
        state: () => queryCommandValue(formatBlock)?.toLowerCase() === "pre",
        result: () => exec(formatBlock, "<pre>"),
    },
    link: {
        icon: createIcon('link'),
        title: "Link",
        result: () => {
            const url = window.prompt("Enter the link URL");
            if (url) exec("createLink", url);
        },
    }
};

function createIcon(name: string): string {
    return `<span class="c-icon c-icon--size-md material-symbols material-symbols-rounded material-symbols-sharp material-symbols-outlined" data-material-symbol="${name}"></span>`;
}

export function queryCommandState(command: string) {
    return document.queryCommandState(command);
}

export function queryCommandValue(command: string): string {
    return document.queryCommandValue(command);
}

let isExecuting = false;

export function exec(command: string, value?: string) {
    if (isExecuting) return;

    try {
        isExecuting = true;
        document.execCommand(command, false, value ?? undefined);
    } finally {
        isExecuting = false;
    }
}
