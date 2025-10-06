interface EditorInterface {
    init(): EditorInterface;
    addOnChange(onChange: (html: string) => void): void;
    getContentArea(): ContentAreaInterface;
    getActionbar(): ActionbarInterface;
    getContent(): string;
    hasContent(): boolean;
}