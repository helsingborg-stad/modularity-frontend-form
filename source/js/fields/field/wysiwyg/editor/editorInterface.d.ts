interface EditorInterface {
    init(): EditorInterface;
    addChangeListeners(listener: (html: string) => void): void;
    getContentArea(): ContentAreaInterface;
    getActionbar(): ActionbarInterface;
    getContent(): string;
    hasContent(): boolean;
}