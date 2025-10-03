interface ActionbarInterface {
    appendActionbar(): HTMLElement;
    getElement(): HTMLElement;
    getActionButtons(): { [key: string]: HTMLButtonElement };
    addActionButtons(actions: EditorActions): void;
    addActionButton(actionButton: EditorAction, name: string): HTMLButtonElement;
}