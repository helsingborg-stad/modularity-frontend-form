import { defaultActions } from "./defaultActions";
import { defaultClasses } from "./defaultCssClasses";

class EditorConfig implements EditorConfigInterface {
    private classes: EditorClasses;
    private actions: EditorActions;
    constructor(private settings: EditorSettings) {
        this.classes = this.setClasses();
        this.actions = this.setActions();
    }

    public getElement(): HTMLElement {
        return this.settings.element;
    }

    public getClasses(): EditorClasses {
        return this.classes;
    }

    public getEditorActions(): EditorActions {
        return this.actions;
    }

    private setActions() {
        let actions: EditorActions = {};
        for (const key in this.settings.actions) {
            if (typeof this.settings.actions[key] === 'string') {
                const actionKey = this.settings.actions[key] as keyof typeof defaultActions;
                actions[key] = defaultActions[actionKey];
            } else {
                actions[key] = this.settings.actions[key];
            }
        }

        return actions;
    }

    private setClasses(): EditorClasses {
        return {
            actionbar: this.settings.classes?.actionbar || defaultClasses.actionbar,
            button: this.settings.classes?.button || defaultClasses.button,
            content: this.settings.classes?.content || defaultClasses.content,
            selected: this.settings.classes?.selected || defaultClasses.selected
        }
    }
}

export default EditorConfig;