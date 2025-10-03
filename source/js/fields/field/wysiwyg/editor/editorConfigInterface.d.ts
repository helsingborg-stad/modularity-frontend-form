interface EditorConfigInterface {
    getElement(): HTMLElement;
    getClasses(): EditorClasses;
    getEditorActions(): EditorActions;
    getOnchange(): (html: string) => void;
}

interface EditorSettings {
    element: HTMLElement;
    onChange: (html: string) => void;
    actions: EditorConfigActions;
    classes?: EditorConfigClasses;
}

type DefaultEditorAction =
    | "bold"
    | "italic"
    | "underline"
    | "strikethrough"
    | "heading1"
    | "heading2"
    | "paragraph"
    | "quote"
    | "olist"
    | "ulist"
    | "code"
    | "link";

interface EditorAction {
    icon: string;
    title: string;
    result: () => void;
    state?: () => boolean | null;
}

interface EditorConfigActions {
    [key: string]: EditorAction | DefaultEditorAction;
}

type EditorClasses = {
    actionbar: string;
    button: string;
    content: string;
    selected: string;
}


type EditorConfigClasses = {
    actionbar?: string;
    button?: string;
    content?: string;
    selected?: string;
}

type EditorActions = {
    [key: string]: EditorAction;
};